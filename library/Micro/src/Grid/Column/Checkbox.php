<?php

namespace Micro\Grid\Column;

use Micro\Grid\Column;

class Checkbox extends Column
{
    protected $checkAll = false;

    public function __construct($name, array $options = array())
    {
        parent::__construct($name, $options);

        if ($this->checkAll) {
            $this->setTitle('<input class="checkbox" data-handler="grid.checkall" type="checkbox" data-rel="' . $this->getName() . '[]" value="1" /> ' . $this->getTitle());
        }
    }

    public function setCheckAll($value)
    {
        $this->checkAll = (bool) $value;
    }

    public function __toString()
    {
        $value = parent::__toString();

        return '<input class="checkbox" type="checkbox" name="' . $this->getName() . '[]" value="' . $value . '" />';
    }
}