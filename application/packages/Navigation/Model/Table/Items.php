<?php

namespace Navigation\Model\Table;

use Micro\Database\Table\TableAbstract;

class Items extends TableAbstract
{
    protected $_name = 'MenuItems';

    public function updateTree(array $tree)
    {
        $this->_updateTree($tree);
    }

    protected function _updateTree(array $tree, $parent = null)
    {
        foreach ($tree as $k => $item) {

            $this->update(array('parentId' => $parent, 'order' => ($k + 1)), array('id = ?' => $item['id']));

            if (!empty($item['children'])) {
                $this->_updateTree($item['children'], $item['id']);
            }
        }
    }
}