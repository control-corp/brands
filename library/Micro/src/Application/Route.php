<?php

namespace Micro\Application;

use Exception as CoreException;
use Micro\Application\Utils;

class Route
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var string
     */
    protected $compiled;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Closure|string
     */
    protected $handler;

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var array
     */
    protected $params = [];
    protected $paramsOptional = [];
    protected $paramsRequired = [];

    /**
     * @param string $name
     * @param string $pattern
     * @param \Closure|string $handler
     */
    public function __construct($name, $pattern, $handler)
    {
        $this->name    = $name;
        $this->pattern = $pattern;

        if ($handler instanceof \Closure) {
            $handler = $handler->bindTo($this);
        }

        $this->handler = $handler;
    }

    /**
     * @param string $pattern
     * @return boolean
     */
    public static function isStatic($pattern)
    {
        return !preg_match('~[{}\[\]]~', $pattern);
    }

    /**
     * @param string $requestUri
     * @return boolean
     */
    public function match($requestUri)
    {
        if (preg_match('~^' . $this->compile() . '$~ius', $requestUri, $matches)) {

            foreach ($this->params as $k => $v) {
                if (isset($matches[$k])) {
                    $this->params[$k] = $matches[$k];
                }
            }

            return \true;
        }

        return \false;
    }

    /**
     * Compile route pattern to regex
     * @return string
     */
    public function compile()
    {
        if ($this->compiled === \null) {

            $this->compiled = $this->pattern;

            $lambdaOptional = function ($match) {

                $regex = '\w+';

                $this->paramsOptional[$match[2]] = isset($this->defaults[$match[2]]) ? $this->defaults[$match[2]] : \null;

                if (isset($this->conditions[$match[2]])) {
                    $regex = $this->conditions[$match[2]];
                }

                return '(' . $match[1] . '(?P<' . $match[2] . '>' . $regex . ')' . $match[3] . ')?';

            };

            $this->compiled = preg_replace_callback('~\[([^\]]*){([^}]+)}([^\]]*)\]~ius', $lambdaOptional->bindTo($this), $this->compiled);

            $lambdaRequired = function ($match) {

                $regex = '\w+';

                $this->paramsRequired[$match[1]] = isset($this->defaults[$match[1]]) ? $this->defaults[$match[1]] : \null;

                if (isset($this->conditions[$match[1]])) {
                    $regex = $this->conditions[$match[1]];
                }

                return '(?P<' . $match[1] . '>' . $regex . ')';

            };

            $this->compiled = preg_replace_callback('~{([^}]+)}~ius', $lambdaRequired->bindTo($this), $this->compiled);

            $this->params = $this->paramsRequired + $this->paramsOptional;
        }

        return $this->compiled;
    }

    /**
     * @param string $compiled
     * @return \Micro\Application\Route
     */
    public function setCompiled($compiled)
    {
        $this->compiled = $compiled;

        return $this;
    }

    /**
     * @param array $data
     * @throws \Exception
     * @return string
     */
    public function assemble(array &$data = [])
    {
        $data += $this->defaults;

        $url = $this->pattern;

        $a = 0;

        if ($data['controller'] === 'brand-classes') {
            $a = 1;
        }

        foreach ($data as $k => $v) {

            $v = Utils::decamelize($v);

            $lambda = function ($match) use ($v) {
                array_shift($match);
                array_shift($match);
                return $match[0] . $v . $match[1];
            };

            $ocount = 0;
            $url = preg_replace_callback('~(\[([^\]]*){' . $k . '}([^\]]*)\])~ius', $lambda, $url, -1, $ocount); // replace optionals

            $count = 0;
            $url = preg_replace('~{' . $k . '}~ius', $v, $url, -1, $count); // replace required

            if ($ocount + $count !== 0) {
                unset($data[$k]);
            }
        }

        $url = preg_replace('~(\[([^\]]+)\])~ius', '', $url); // clear rest optionals

        if (!static::isStatic($url)) { // check something wrong
            throw new CoreException(sprintf('Too few arguments? "%s"!', $url), 500);
        }

        return $url;
    }

    /**
     * @return \Closure|string
     */
    public function getHandler($invoke = \true)
    {
        if ($invoke === \true && $this->handler instanceof \Closure) {
            return $this->handler->__invoke();
        }

        return $this->handler;
    }

    /**
     * @param array $conditions
     * @return \Micro\Application\Route
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param array $defaults
     * @return \Micro\Application\Route
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param string $pattern
     * @return \Micro\Application\Route
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function getParams($withDefaults = \true)
    {
        if ($withDefaults === \true) {
            return $this->params + $this->defaults;
        }

        return $this->params;
    }

    /**
     * @param array $params
     * @return \Micro\Application\Route
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}