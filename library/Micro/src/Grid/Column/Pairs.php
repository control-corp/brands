<?php

namespace Micro\Grid\Column;

use Micro\Grid\Column;

class Pairs extends Column
{
    protected $pairs;
    protected $callable;
    protected $params = array();

    public function setCallable($value)
    {
        $this->callable = $value;
    }

    public function setParams(array $value)
    {
        $this->params = $value;
    }

    public function setFalse($false)
    {
        $this->false = $false;
    }

    public function __toString()
    {
        $value = parent::__toString();

        $pairs = array();

        if ($this->callable !== null && is_callable($this->callable)) {
            if ($this->pairs === null) {
                $this->pairs = call_user_func_array($this->callable, $this->params);
            }
        } else {
            $this->pairs = array();
        }

        return isset($this->pairs[$value]) ? $this->pairs[$value] : '';
    }
}