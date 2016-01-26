<?php

namespace Micro\Grid;

use Exception as CoreException;
use Micro\Paginator\Paginator;
use Micro\Form\Element;
use Micro\Application\View;

class Grid
{
    const PLACEMENT_NONE   = 0;
    const PLACEMENT_BOTTOM = 0x1;
    const PLACEMENT_TOP    = 0x2;
    const PLACEMENT_BOTH   = 0x3;

    protected $columns = [];
    protected $buttons = [];

    protected $paginator;
    protected $view;
    protected $gridClass;
    protected $paginationViewScript = 'paginator';
    protected $paginatorAlways = \true;
    protected $paginatorPlacement = self::PLACEMENT_BOTTOM;
    protected $buttonsPlacement = self::PLACEMENT_TOP;
    protected $sortedColumn;

    public function __construct($paginator, $options)
    {
        if ($paginator !== \null && !($paginator instanceof Paginator)) {
            try {
                $paginator = new Paginator($paginator);
            } catch (\Exception $e) {
                throw new CoreException('Invalid paginator in ' . __METHOD__);
            }
        }

        $this->paginator = $paginator;

        if (is_string($options) && file_exists($options)) {
            $options = include $options;
            $options = is_array($options) ? $options : [];
        }

        if (!is_array($options)) {
            throw new CoreException('Invalid options in ' . __METHOD__ . ' (' . (is_string($options) ? $options : json_encode($options)) . ')');
        }

        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
    }

    public function getGridClass()
    {
        return $this->gridClass;
    }

    public function setGridClass($class)
    {
        $this->gridClass = $class;
    }

    public function getView()
    {
        if (\null === $this->view) {
            $this->view = new View(\null);
            try {
                $paths = (array) package_path(current_package(), 'views');
            } catch (\Exception $e) {
                $paths = [];
            }
            $this->view->injectPaths($paths);
        }

        return $this->view;
    }

    public function getPaginationViewScript()
    {
        return $this->paginationViewScript;
    }

    public function setPaginationViewScript($value)
    {
        $this->paginationViewScript = $value;

        return $this;
    }

    public function getPaginatorAlways()
    {
        return $this->paginatorAlways;
    }

    public function setPaginatorAlways($value)
    {
        $this->paginatorAlways = $value;

        return $this;
    }

    /**
     * @param \Micro\Grid\Column $column
     * @return $this
     */
    public function setSortedColumn(Column $column)
    {
        $this->sortedColumn = $column;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortedColumn()
    {
        return $this->sortedColumn;
    }

    public function setSorted($columnName, $direction = 'asc')
    {
        $column = $this->getColumn($columnName);

        if (!$column instanceof Column || !$column->isSortable()) {
            return \false;
        }

        if ($this->getSortedColumn()) {
            $this->getSortedColumn()->clearSorted();
        }

        $column->setSortable(\true);

        $column->setSorted($direction);

        $this->setSortedColumn($column);

        return true;
    }

    public function setPaginatorPlacement($placement = self::PLACEMENT_BOTTOM)
    {
        if (is_numeric($placement)) {
            $this->paginatorPlacement = (int) $placement;
        } else if (is_string($placement)) {
            $const = 'PLACEMENT_' . strtoupper($placement);
            if (!defined("self::$const")) {
                throw new CoreException('Invalid paginator placement');
            }
            $this->paginatorPlacement = constant("self::$const");
        } else {
            throw new CoreException('Invalid paginator placement');
        }

        return $this;
    }

    public function getPaginatorPlacement()
    {
        return $this->paginatorPlacement;
    }

    public function setButtonsPlacement($placement = self::PLACEMENT_TOP)
    {
        if (is_numeric($placement)) {
            $this->buttonsPlacement = (int) $placement;
        } else if (is_string($placement)) {
            $const = 'PLACEMENT_' . strtoupper($placement);
            if (!defined("self::$const")) {
                throw new CoreException('Invalid paginator placement');
            }
            $this->buttonsPlacement = constant("self::$const");
        } else {
            throw new CoreException('Invalid buttons placement');
        }

        return $this;
    }

    public function getButtonsPlacement()
    {
        return $this->buttonsPlacement;
    }

    public function getColumn($name)
    {
        if (isset($this->columns[$name])) {
            return $this->columns[$name];
        }

        return \null;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns(array $columns)
    {
        $this->clearColumns();

        $this->addColumns($columns);

        return $this;
    }

    public function addColumns(array $columns)
    {
        foreach ($columns as $key => $spec) {

            $name = \null;

            if (!is_numeric($key)) {
                $name = $key;
            }

            if (is_string($spec) || ($spec instanceof Column)) {
                $this->addColumn($spec, $name);
                continue;
            }

            $options = [];

            $type = 'column';

            if (is_array($spec)) {
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                } else {
                    $type = 'column';
                }
                if (isset($spec['name'])) {
                    $name = $spec['name'];
                }
                if (isset($spec['options'])) {
                    $options = $spec['options'];
                }
            }

            $this->addColumn($type, $name, $options);
        }

        return $this;
    }

    public function addColumn($type, $name = \null, $options = \null)
    {
        $columnObj = \null;

        if (is_string($type)) {
            if (\null === $name) {
                throw new CoreException('Columns specified by string must have an accompanying name');
            }
            $columnObj = $this->createColumn($type, $name, $options);
        } elseif ($type instanceof Column) {
            if (\null === $name) {
                $name = $type->getName();
            }
            $columnObj = $type;
        }

        if (\null === $columnObj) {
            throw new CoreException('Cannot add NULL column to grid');
        }

        if (!($columnObj instanceof Column)) {
            throw new CoreException('Trying to add invalid column to grid');
        }

        $columnObj->setGrid($this);

        $this->columns[$name] = $columnObj;

        return $this;
    }

    public function createColumn($type, $name, array $options = \null)
    {
        if (!is_string($type)) {
            throw new CoreException('Column type must be a string indicating type');
        }

        if ($options === \null) {
            $options = [];
        }

        if (class_exists($type)) {
            return new $type($name, $options);
        }

        $type = ucfirst($type);

        if ($type === 'Column') {
            $columnClass = 'Micro\Grid\\' . $type;
        } else {
            $columnClass = 'Micro\Grid\Column\\' . $type;
        }

        if (!class_exists($columnClass)) {
            throw new CoreException("Class '{$columnClass}' does not exists");
        }

        return new $columnClass($name, $options);
    }

    public function removeColumn($name)
    {
        if (isset($this->columns[(string) $name])) {
            unset($this->columns[(string) $name]);
        }

        return $this;
    }

    public function clearColumns()
    {
        $this->columns = [];

        return $this;
    }

    public function setButtons(array $buttons)
    {
        $this->clearButtons();

        $this->addButtons($buttons);

        return $this;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    public function addButtons(array $buttons)
    {
        foreach ($buttons as $name => $button) {

            if (is_numeric($name)) {
                throw new CoreException('Invalid button configuration passed');
            }

            if (!isset($button['value'])) {
                $button['value'] = ucfirst($name);
            }

            $this->addButton($name, $button);
        }

        return $this;
    }

    public function addButton($name, array $options = \null)
    {
        if ($options === \null) {
            $options = [];
        }

        $this->buttons[$name] = $options;

        return $this;
    }

    public function clearButtons()
    {
        $this->buttons = [];

        return $this;
    }

    public function removeButton($name)
    {
        if (isset($this->buttons[(string) $name])) {
            unset($this->buttons[(string) $name]);
        }

        return $this;
    }

    public function setPageNumber($page)
    {
        $this->paginator->setPageNumber((int) $page);

        return $this;
    }

    public function setIpp($ipp)
    {
        $this->paginator->setIpp($ipp);

        return $this;
    }

    public function getPaginator()
    {
        return $this->paginator;
    }

    public function render()
    {
        $paginator = $this->getPaginator();

        $output = '';
        $buttonsCode = '';

        $request = app('request');
        $requestParams = $request->getParams();
        $requestParams = array_diff_key($requestParams, $request->getPost());

        foreach ($requestParams as $key => $requestParam) {
            if (!is_array($requestParam)) {
                continue;
            }
            unset($requestParams[$key]);
        }

        if (!empty($this->buttons)) {
            $output .= '<form class="grid-form" method="post" action="' . route() . '">';
            $buttonsCode = '<div class="grid-buttons">';
            foreach ($this->buttons as $name => $button) {
                try {
                    $button = new Element\Submit($name, $button);
                    $buttonsCode .= $button->render() . ' ';
                } catch (\Exception $e) {
                    $buttonsCode .= $e->getMessage();
                }
            }
            $buttonsCode .= '</div>';
            if ($this->getButtonsPlacement() & self::PLACEMENT_TOP) {
                $output .= $buttonsCode;
            }
        }

        if ($this->getPaginatorPlacement() & self::PLACEMENT_TOP) {
            $output .= $this->renderPagination();
        }

        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-bordered table-hover' . ($this->getGridClass() ? ' ' . $this->getGridClass() : '') . '">';
        $output .= '<thead>';

        $output .= '<tr class="table-row-head">';

        foreach ($this->columns as $column) {

            if (!$column instanceof Column) {
                continue;
            }

            if ($column->isSortable()) {
                $sortedClass = 'sorting';
                if ($column->isSorted()) {
                    $sortedClass = 'sorting_' . $column->getSorted();
                }
                $routeParams = array_merge($requestParams, ['orderField' => $column->getName(),
                                                            'orderDir'   => ($column->getSorted() == 'asc') ? 'desc' : 'asc']);
                $title = '<div class="' . $sortedClass . '" data-url="' . route(\null, $routeParams, \false, \true) . '">' . $column->getTitle() . '</div>';
            } else {
                $title = $column->getTitle();
            }

            $output .= '<th' . ($column->getHeadStyle() ? ' style="' . $column->getHeadStyle() . '"' : '') . ' class="table-cell-head' . ($column->getHeadClass() ? ' ' . $column->getHeadClass() : '') . '">';
            $output .= $title;
            $output .= '</th>';
        }

        $output .= '</tr>';
        $output .= '</thead>';

        $output .= '<tbody>';

        foreach ($paginator as $key => $page) {

            $output .= '<tr class="table-row">';

            foreach ($this->columns as $column) {
                if (!$column instanceof Column) {
                    continue;
                }
                try {
                    $output .= '<td' . ($column->getStyle() ? ' style="' . $column->getStyle() . '"' : '') . ' class="table-cell' . ($column->getClass() ? ' ' . $column->getClass() : '') . '">' . (string) $column . '</td>';
                } catch (\Exception $e) {
                    $output .= '<td' . ($column->getStyle() ? ' style="' . $column->getStyle() . '"' : '') . ' class="table-cell' . ($column->getClass() ? ' ' . $column->getClass() : '') . '">' . $e->getMessage() . '</td>';
                }
            }

            $output .= '</tr>';
        }

        $output .= '</tbody>';

        $output .= '</table>';
        $output .= '</div>';


        if ($this->getPaginatorPlacement() & self::PLACEMENT_BOTTOM) {
            $output .= $this->renderPagination();
        }

        if (!empty($this->buttons)) {
            if (($this->getButtonsPlacement() & self::PLACEMENT_BOTTOM)) {
                $output .= $buttonsCode;
            }
            $output .= '</form>';
        }

        return $output;
    }

    public function renderPagination()
    {
        if (($this->getPaginator()->count() <= 1) && !$this->getPaginatorAlways()) {
            return "";
        }

        return pagination($this->getPaginator(), $this->getPaginationViewScript(), \null, $this->getView());
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            if (env('development')) {
                return $e->getMessage();
            }
            return '';
        }
    }
}