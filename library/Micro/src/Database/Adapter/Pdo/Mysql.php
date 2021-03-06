<?php

namespace Micro\Database\Adapter\Pdo;

use Micro\Database\Database;
use Micro\Database\Adapter\Exception as AdapterException;

class Mysql extends PdoAbstract
{
    /**
     * PDO type.
     *
     * @var string
     */
    protected $_pdoType = 'mysql';

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * \Micro\Database::INT_TYPE, \Micro\Database::BIGINT_TYPE, or \Micro\Database::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        Database::INT_TYPE    => Database::INT_TYPE,
        Database::BIGINT_TYPE => Database::BIGINT_TYPE,
        Database::FLOAT_TYPE  => Database::FLOAT_TYPE,
        'INT'                  => Database::INT_TYPE,
        'INTEGER'              => Database::INT_TYPE,
        'MEDIUMINT'            => Database::INT_TYPE,
        'SMALLINT'             => Database::INT_TYPE,
        'TINYINT'              => Database::INT_TYPE,
        'BIGINT'               => Database::BIGINT_TYPE,
        'SERIAL'               => Database::BIGINT_TYPE,
        'DEC'                  => Database::FLOAT_TYPE,
        'DECIMAL'              => Database::FLOAT_TYPE,
        'DOUBLE'               => Database::FLOAT_TYPE,
        'DOUBLE PRECISION'     => Database::FLOAT_TYPE,
        'FIXED'                => Database::FLOAT_TYPE,
        'FLOAT'                => Database::FLOAT_TYPE
    );

    /**
     * Override _dsn() and ensure that charset is incorporated in mysql
     * @see \Micro\Database\Adapter\Pdo\PdoAbstract::_dsn()
     */
    protected function _dsn()
    {
        $dsn = parent::_dsn();
        if (isset($this->_config['charset'])) {
            $dsn .= ';charset=' . $this->_config['charset'];
        }
        return $dsn;
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @return void
     * @throws \Micro\Database\Adapter\Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        if (!empty($this->_config['charset'])) {
            $initCommand = "SET NAMES '" . $this->_config['charset'] . "'";
            $this->_config['driver_options'][1002] = $initCommand; // 1002 = PDO::MYSQL_ATTR_INIT_COMMAND
        }

        parent::_connect();
    }

    /**
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return "`";
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->fetchCol('SHOW TABLES');
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        // @todo  use INFORMATION_SCHEMA someday when MySQL's
        // implementation has reasonably good performance and
        // the version with this improvement is in wide use.

        if ($schemaName) {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName", true);
        } else {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier($tableName, true);
        }
        $stmt = $this->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $result = $stmt->fetchAll(Database::FETCH_NUM);

        $field   = 0;
        $type    = 1;
        $null    = 2;
        $key     = 3;
        $default = 4;
        $extra   = 5;

        $desc = array();
        $i = 1;
        $p = 1;
        foreach ($result as $row) {
            list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
                = array(null, null, null, null, false, null, false);
            if (preg_match('/unsigned/', $row[$type])) {
                $unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'decimal';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'float';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                // The optional argument of a MySQL int type is not precision
                // or length; it is only a hint for display width.
            }
            if (strtoupper($row[$key]) == 'PRI') {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] == 'auto_increment') {
                    $identity = true;
                } else {
                    $identity = false;
                }
                ++$p;
            }
            $desc[$this->foldCase($row[$field])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row[$field]),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $row[$default],
                'NULLABLE'         => (bool) ($row[$null] == 'YES'),
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => $unsigned,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param  string $sql
     * @param  integer $count
     * @param  integer $offset OPTIONAL
     * @throws \Micro\Database\Adapter\Exception
     * @return string
     */
     public function limit($sql, $count, $offset = 0)
     {
        $count = intval($count);
        if ($count <= 0) {
            throw new AdapterException("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new AdapterException("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }
}