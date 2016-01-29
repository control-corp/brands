<?php

namespace Micro\Grid\Column;

use Micro\Grid\Column;

class Href extends Column
{
    protected $params = [];
    protected $reset = \false;
    protected $qsa = \true;
    protected $hrefClass = '';

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function setReset($value)
    {
        $this->reset = (bool) $value;
    }

    public function setQsa($value)
    {
        $this->qsa = (bool) $value;
    }

    public function setHrefClass($value)
    {
        $this->hrefClass = $value;
    }

    public function __toString()
    {
        try {

            $params = $this->params;
            $route  = isset($params['route']) ? $params['route'] : \null;

            unset($params['route']);

            foreach ($params as $k => $v) {
                if (substr($v, 0, 1) === ':') {
                    $field = substr($v, 1);
                    $params[$k] = $this->getCurrentValue($field);
                }
            }

            return '<a' . ($this->hrefClass ? ' class="' . $this->hrefClass . '"' : '') . ' href="' . app('router')->assemble($route, $params, $this->reset, $this->qsa) . '">' . parent::__toString() . '</a>';

        } catch (\Exception $e) {
            if (env('development')) {
                return $e->getMessage();
            }
        }
    }
}