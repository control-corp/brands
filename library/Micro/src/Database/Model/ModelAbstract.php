<?php

namespace Micro\Database\Model;

use Micro\Database\Table\Row\RowAbstract;
use Micro\Database\Table\TableAbstract;
use Micro\Database\Table\Select;
use Micro\Database\Expr;

abstract class ModelAbstract extends TableAbstract
{
    protected $dependentWhere = [];

    protected static $transactionLevel = 0;

    public function setDependentWhere($where)
    {
        $this->dependentWhere = $where;

        return $this;
    }

    public function createEntity($data)
    {
        $primaries = [];

        foreach ($this->info('primary') as $primaryKey) {
            $primaries[$primaryKey] = \null;
        }

        foreach ($this->getDependentTables() as $dependentTable) {
            $dependentTableInstance = $this->getDependentTableInstance($dependentTable);
            foreach ($dependentTableInstance->info('primary') as $primaryKey) {
                $primaries[$primaryKey] = \null;
            }
        }

        $rowClass = $this->getRowClass();

        $row = new $rowClass(array(
            'table'    => $this,
            'data'     => $data + $primaries,
            'readOnly' => false,
            'stored'   => false
        ));

        return $row;
    }

    /**
     * @param mixed $where
     * @param mixed $order
     * @param mixed $count
     * @param mixed $offset
     * @throws \Exception
     * @return \Micro\Database\Select
     */
    public function buildSelect($where = \null, $order = \null, $count = \null, $offset = \null)
    {
        if (!($where instanceof Select)) {

            $select = $this->select();

            if ($where !== \null) {
                $this->_where($select, $where);
            }

            if ($order !== \null) {
                $this->_order($select, $order);
            }

            if ($count !== \null || $offset !== \null) {
                $select->limit($count, $offset);
            }

        } else {

            $select = $where;
        }

        $select->from($this);

        $select->setIntegrityCheck(\false);

        foreach ($this->getDependentTables() as $dependentTable) {

            $dependentTableInstance = $this->getDependentTableInstance($dependentTable);

            $dependentTableInfo = $dependentTableInstance->info();

            $reference = $dependentTableInstance->getReference(get_class($this));

            $onCondition = [];

            foreach ($reference['columns'] as $k => $column) {

                $refColumn = $reference['refColumns'][$k];

                $onCondition[] = $this->_db->quoteIdentifier($this->_name) . '.' . $this->_db->quoteIdentifier($refColumn) . ' = ' . $this->_db->quoteIdentifier($dependentTableInfo['name']) . '.' . $this->_db->quoteIdentifier($column);
            }

            if (isset($this->dependentWhere[$dependentTableInfo['name']])) {
                foreach ($this->dependentWhere[$dependentTableInfo['name']] as $k => $v) {
                    if (in_array($k, $dependentTableInfo['cols'])) {
                        $onCondition[] = $this->_db->quoteIdentifier($dependentTableInfo['name']) . '.' . $this->_db->quoteIdentifier($k) . ' = ' . $this->_db->quote($v);
                    }
                }
            }

            if (empty($onCondition)) {
                throw new \Exception('Cannot join tables', 500);
            }

            $joinType = 'joinLeft';

            if (isset($reference['joinType'])) {
                switch ($reference['joinType']) {
                    case 'inner' :
                        $joinType = 'joinInner';
                        break;
                }
            }

            $select->$joinType($dependentTableInfo['name'], implode(" AND ", $onCondition), array_diff($dependentTableInfo['cols'], $this->_cols));
        }

        return $select;
    }

    /**
     * @param mixed $where
     * @param mixed $order
     * @param mixed $count
     * @param mixed $offset
     * @throws \Exception
     * @return \Micro\Database\Table\Rowset\RowsetAbstract
     */
    public function getItems($where = \null, $order = \null, $count = \null, $offset = \null)
    {
        return $this->fetchAll(
            $this->buildSelect($where, $order, $count, $offset)
        );
    }

    public function getItem()
    {
        $primary = $this->info('primary');
        $args = func_get_args();

        $where = array();

        foreach (array_values($primary) as $k => $key) {
            $where[$key . ' = ?'] = $args[$k];
        }

        $items = $this->getItems($where, \null, 1, 0);

        return $items->current();
    }

    public function save($entity)
    {
        if (is_array($entity)) {
            $entity = $this->createEntity($entity);
        }

        return $this->saveEntity($entity);
    }

    protected function saveEntity(RowAbstract $entity)
    {
        $this->trigger('beforesave', compact('entity'));

        $typeData = $entity->toArray();

        $pkData = $this->info(TableAbstract::PRIMARY);

        $pkData = array_combine($pkData, $pkData);

        $originalData = $entity->toArray();

        if (empty($originalData[current($pkData)])) {
            unset($originalData[current($pkData)]);
        }

        try {

            $this->beginTransaction();

            $data = $this->saveToTable($this, $typeData);

            $typeData = array_merge($typeData, $data);

            foreach ($this->getDependentTables() as $dependentTable) {

                $dependentTableInstance = $this->getDependentTableInstance($dependentTable);

                $reference = $dependentTableInstance->getReference(get_class($this));

                foreach ($typeData as $k => $v) {
                    $refColumnKey = array_search($k, $reference['refColumns']);
                    if ($refColumnKey !== \false) {
                        $typeData[$reference['columns'][$refColumnKey]] = $v;
                    }
                }

                $data = $this->saveToTable($dependentTableInstance, $typeData);

                $typeData = array_merge($typeData, $data);
            }

            $entity->setFromArray($typeData);

            $this->trigger('aftersave', compact('entity'));

            $this->commit();

        } catch (\Exception $e) {

            $this->rollback();

            $entity->setFromArray($originalData);

            throw $e;
        }

        return array_intersect_key($entity->toArray(), $pkData);
    }

    protected function saveToTable(TableAbstract $table, $data)
    {
        /**
         * Force primary keys as array in case we use compound primary key
         */
        $primaryKeys = (array) $table->info(TableAbstract::PRIMARY);

        $primaryValues = [];

        $primaryValuesFilled = \false;

        foreach ($primaryKeys as $primaryKey) {

            if (!array_key_exists($primaryKey, $data)) {
                throw new \Exception(get_class($table) . 'Primary key doesn\'t match object type properties');
            }

            if (!empty($data[$primaryKey])) {
                $primaryValuesFilled = \true;
            }

            $primaryValues[] = $data[$primaryKey];
        }

        $rowSet = \null;

        /**
         * Try to find row from primary table based on primary key(s) value(s)
         */
        if ($primaryValuesFilled) {
            $rowSet = call_user_func_array(array($table, 'find'), $primaryValues);
        }

        /**
         * Check if primary key is a sequence and exclude it from data
         * @todo \Micro\Database\Table\TableAbstract seems to set sequence = true even
         *       if primary key is compound containing foreign sequence
         */
        if ($table->info(TableAbstract::SEQUENCE) && (count($primaryKeys) == 1)) {
            $tempArray = array_flip($primaryKeys);
            $data = array_diff_key($data, $tempArray);
        }

        /**
         * Get current row from rowset or fetch new one if we'll insert
         */
        if ($rowSet && $rowSet->count()) {
            $row = $rowSet->current();
        } else {
            $row = $table->createRow();
        }

        try {
            foreach ($data as $column => $value) {
                if ($value instanceof \DateTime) {
                    $data[$column] = $value->format('Y-m-d H:i:s');
                }
            }
            $row->setFromArray($data);
            $row->save();
        } catch (\Exception $e) {
            throw $e;
        }

        /**
         * Merge type data with possible new info from database
         */
        $data = array_merge($data, $row->toArray());

        return $data;
    }

    public function fetchPairs($where = \null, $order = \null, $count = \null, $offset = \null, $columns = \null)
    {
        $select = $this->buildSelect($where, $order, $count, $offset);

        $select->reset('columns');

        if ($columns !== \null) {
            $select->columns($columns);
        } else {
            $select->columns(['id', new Expr('name')]);
        }

        return $this->_db->fetchPairs($select);
    }

    public function beginTransaction()
    {
        if (self::$transactionLevel === 0) {
            $this->_db->beginTransaction();
        }

        self::$transactionLevel++;
    }

    public function commit()
    {
        if (self::$transactionLevel === 1) {
            $this->_db->commit();
        }

        self::$transactionLevel--;
    }

    public function rollback()
    {
        if (self::$transactionLevel === 1) {
            $this->_db->rollBack();
        }

        self::$transactionLevel--;
    }

    protected function trigger($event, array $params = null)
    {
        $event = str_replace('\\', '.', get_class($this)) . '.' . ucfirst($event);

        app('event')->trigger($event, $params);
    }
}