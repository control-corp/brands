<?php

namespace Micro\Application;

class Route
{
    protected $pattern;
    protected $conditions = [];
    protected $compiled;
    protected $name;
    protected $handler;
    protected $defaults = [];
    protected $params = [];

    public function __construct($name, $pattern, $handler)
    {
        $this->name    = $name;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public static function isStatic($pattern)
    {
        return !preg_match('~[{}\[\]]~', $pattern);
    }

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

    public function compile()
    {
        if ($this->compiled === \null) {

            $this->compiled = $this->pattern;

            $lambdaOptional = function ($match) {

                $regex = '[\-\w]+';

                $this->params[$match[2]] = isset($this->defaults[$match[2]]) ? $this->defaults[$match[2]] : \null;

                if (isset($this->conditions[$match[2]])) {
                    $regex = $this->conditions[$match[2]];
                }

                return '(' . $match[1] . '(?P<' . $match[2] . '>' . $regex . ')' . $match[3] . ')?';

            };

            $lambdaOptional = $lambdaOptional->bindTo($this);

            $this->compiled = preg_replace_callback('~\[([^\]]*){([^}]+)}([^\]]*)\]~ius', $lambdaOptional, $this->compiled);

            $lambdaRequired = function ($match) {

                $regex = '[\-\w]+';

                $this->params[$match[1]] = isset($this->defaults[$match[1]]) ? $this->defaults[$match[1]] : \null;

                if (isset($this->conditions[$match[1]])) {
                    $regex = $this->conditions[$match[1]];
                }

                return '(?P<' . $match[1] . '>' . $regex . ')';

            };

            $lambdaRequired = $lambdaRequired->bindTo($this);

            $this->compiled = preg_replace_callback('~{([^}]+)}~ius', $lambdaRequired, $this->compiled);
        }

        return $this->compiled;
    }

    public function assemble(array &$data = [])
    {
        $data += $this->defaults;

        $url = $this->pattern;

        foreach ($data as $k => $v) {

            if (is_array($v) || is_object($v)) {
                unset($data[$k]);
                continue;
            }

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
            throw new \Exception(sprintf('Too few arguments? "%s"!', $url), 500);
        }

        return $url;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }
}