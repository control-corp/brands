<?php

namespace Micro\Container;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var array
     */
    protected $resolved = [];

    /**
     * @var \Micro\Container
     */
    protected static $instance;

    /**
     * @param \Micro\Container $instance
     */
    public static function setInstance(Container $instance)
    {
        static::$instance = $instance;
    }

    /**
     * @return \Micro\Container
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (!isset($this->services[$offset])) {
            throw new \InvalidArgumentException(sprintf('Container key "%s" does\'n exists', $offset), 500);
        }

        // call resolved
        if (isset($this->resolved[$offset])) {
            return $this->call($this->resolved[$offset]);
        }

        $result = $this->services[$offset];

        if ($result instanceof \Closure) {
            $result = $result->__invoke($this);
        }

        if ($result instanceof ContainerAwareInterface) {
            $result->setContainer($this);
        }

        $this->resolved[$offset] = $result;

        return $this->call($result);
    }

    /**
     * @param mixed $service
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function call($service)
    {
        if (is_object($service) && method_exists($service, '__invoke')) {
            return $service->__invoke($this);
        }

        return $service;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->services[$offset])) {
            throw new \InvalidArgumentException(sprintf('Container key "%s" already exists!', $offset), 500);
        }

        $this->services[$offset] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->services[$offset]);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (isset($this->services[$offset])) {
            unset($this->services[$offset]);
        }

        if (isset($this->resolved[$offset])) {
            unset($this->resolved[$offset]);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Container\ContainerInterface::get()
     */
    public function get($service)
    {
        return $this->offsetGet($service);
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Container\ContainerInterface::set()
     */
    public function set($service, $callback)
    {
        return $this->offsetSet($service, $callback);
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Container\ContainerInterface::has()
     */
    public function has($service)
    {
        return $this->offsetExists($service);
    }
}