<?php

namespace Micro\Grid;

use Exception as CoreException;

class Column
{
    protected $grid;
    protected $title;
    protected $name;
    protected $text;
    protected $class = '';
    protected $headClass = '';
    protected $style = '';
    protected $headStyle = '';
    protected $sourceField;
    protected $viewScript;
    protected $partial;
    protected $filter;

    protected $sortable = false;
    protected $sorted;

    public function __construct($name, array $options = array())
    {
        if (!is_string($name)) {
            throw new CoreException('Column name must be string');
        }

        $this->name = $name;

        foreach ($options as $optionName => $optionValue) {
            $method = 'set' . ucfirst($optionName);
            if (method_exists($this, $method)) {
                $this->$method($optionValue);
            }
        }
    }

    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    public function setViewScript($value)
    {
        $this->viewScript = $value;

        return $this;
    }

    public function setPartial($value)
    {
        $this->partial = $value;

        return $this;
    }

    public function setText($value)
    {
        $this->text = $value;

        return $this;
    }

    public function setClass($value)
    {
        $this->class = $value;

        return $this;
    }

    public function setFilter($value)
    {
        $this->filter = $value;

        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setHeadClass($value)
    {
        $this->headClass = $value;

        return $this;
    }

    public function getHeadClass()
    {
        return $this->headClass;
    }

    public function setHeadStyle($value)
    {
        $this->headStyle = $value;

        return $this;
    }

    public function getHeadStyle()
    {
        return $this->headStyle;
    }

    public function setStyle($value)
    {
        $this->style = $value;

        return $this;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function getGrid()
    {
        return $this->grid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSourceField()
    {
        if (empty($this->sourceField) && $this->text === null) {
            throw new CoreException('Column is not bound to any source field');
        }

        return $this->sourceField;
    }

    public function setSourceField($value)
    {
        $this->sourceField = trim($value);

        return $this;
    }

    public function setTitle($value)
    {
        $this->title = $value;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setSorted($sorted = 'asc')
    {
        if (strtolower($sorted) == 'asc') {
            $this->sorted = 'asc';
        } else {
            $this->sorted = 'desc';
        }

        return $this;
    }

    public function getSorted()
    {
        return $this->sorted;
    }

    public function isSorted()
    {
        return ($this->sorted !== null);
    }

    public function clearSorted()
    {
        $this->sorted = null;

        return $this;
    }

    public function setSortable($sortable)
    {
        $this->sortable = (bool) $sortable;

        return $this;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function getCurrentItem()
    {
        return $this->getGrid()->getPaginator()->getIterator()->current();
    }

    public function getCurrentValue($field = null)
    {
        if ($this->text !== null && $field === null) {
            return $this->text;
        }

        if ($field === null) {
            $field = $this->getSourceField();
        }

        $item  = $this->getCurrentItem();
        $value = '';

        if (is_array($item) || $item instanceof \ArrayAccess) {
            if (isset($item[$field])) {
                $value = $item[$field];
            }
        } else if (is_object($item)) {
            if (isset($item->{$field})) {
                $value = $item->{$field};
            } else if (method_exists($item, 'get' . ucfirst($field))) {
                $method = 'get' . ucfirst($field);
                $value = $item->$method();
            } else {
                throw new CoreException("Column {$this->getTitle()} is bound to non existent object property: {$field}");
            }
        } else {
            $value = $item;
        }

        if ($this->filter !== \null) {
            if (is_callable($this->filter)) {
                $value = call_user_func($this->filter, $value);
            } else if (is_array($this->filter) && isset($this->filter['callback']) && is_callable($this->filter['callback'])) {
                $value = call_user_func_array($this->filter['callback'], array('value' => $value) + (isset($this->filter['params']) ? $this->filter['params'] : array()));
            }
        }

        return $value;
    }

    public function __toString()
    {
        try {

            $value = $this->getCurrentValue();

            if ($this->viewScript) {

                $view = $this->getGrid()->getView();

                $data = array(
                    'value' => $value,
                    'item'  => $this->getCurrentItem()
                );

                if ($this->partial) {
                    $value = (string) $view->partial($this->viewScript, $data);
                } else {
                    $value = (string) $view->addData($data)
                                           ->render($this->viewScript);
                }
            }

        } catch (\Exception $e) {
            $value = $e->getMessage();
        }

        return (string) $value;
    }
}