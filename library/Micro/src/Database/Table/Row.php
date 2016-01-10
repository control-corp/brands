<?php

namespace Micro\Database\Table;

use Micro\Database\Expr;
use Micro\Auth\Auth;

class Row extends Row\RowAbstract
{
    /**
     * @return \Micro\Database\Table\Row
     */
    public function beforeSave()
    {
        return $this;
    }

    /**
     * @return \Micro\Database\Table\Row
     */
    public function afterSave()
    {
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Database\Table\Row\RowAbstract::save()
     */
    public function save()
    {
        if ($this->_table) {
            $cols = $this->_table->info(TableAbstract::COLS);
        } else {
            $cols = array();
        }

        if (empty($this->_cleanData) && in_array('created', $cols)) {
            $this->created = new Expr('NOW()');
        }

        if (in_array('edited', $cols)) {
            $this->edited = new Expr('NOW()');
        }

        $identity = Auth::identity();

        if ($identity && method_exists($identity, 'getId')) {
            if (in_array('owner', $cols) && (empty($this->_cleanData) || !$this->owner)) {
                $this->owner = $identity->getId();
            }
            if (in_array('editor', $cols)) {
                $this->editor = $identity->getId();
            }
        }

        return parent::save();
    }
}