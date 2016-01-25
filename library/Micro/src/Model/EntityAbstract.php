<?php

namespace Micro\Model;

use Micro\Application\Utils;

abstract class EntityAbstract implements \ArrayAccess
{
    public function setFromArray(array $data)
    {
        $reflection = new \ReflectionClass($this);

        foreach ($data as $k => $v) {
            $method = 'set' . ucfirst(Utils::camelize($k));
            if ($reflection->hasMethod($method)) {
                $this->$method($v);
            } else if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    public function toArray()
    {
        $data = array();

        $reflection = new \ReflectionClass($this);

        foreach ($this as $k => $v) {
            $method = 'get' . ucfirst(Utils::camelize($k));
            if ($reflection->hasMethod($method)) {
                $data[$k] = $this->$method();
            } else {
                $data[$k] = $v;
            }
        }

        return $data;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function __toString()
    {
        return get_class($this);
    }

    public function offsetExists ($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * @param mixed $offset
     * @return \null
     */
    public function offsetGet ($offset)
    {
        return $this->offsetExists($offset)
               ? $this->$offset
               : \null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet ($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset ($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = \null;
        }
    }

    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    public function __set($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->offsetSet($offset, $value);
        }
    }
}