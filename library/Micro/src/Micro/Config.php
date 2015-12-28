<?php

namespace Micro;

class Config
{
    protected static $config = [];

    public function __construct($data = \null)
    {
        $this->load($data);
    }

    public function load(array $data = \null)
    {
        if ($data === \null) {
            return $this;
        }

        static::$config = array_replace_recursive(static::$config, $data);

        return $this;
    }

    public function get($prop, $default = \null)
    {
        $config = static::$config;

        foreach (explode('.', $prop) as $key) {
            if (!isset($config[$key])) {
                return $default;
            }
            $config = &$config[$key];
        }

        return $config;
    }
}