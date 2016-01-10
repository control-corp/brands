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
     * @var array
     */
    protected $aliases = [];

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
        /**
         * Check if it has an alias
         */
        if (isset($this->aliases[$offset])) {
            $offset = $this->resolveAlias($offset);
        }

        if (!isset($this->services[$offset])) {
            throw new \InvalidArgumentException(sprintf('[' . __METHOD__ . '] Service "%s" not found!', $offset), 500);
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
        if (isset($this->resolved[$offset])) {
            throw new \InvalidArgumentException(sprintf('[' . __METHOD__ . '] Service "%s" is resolved!', $offset), 500);
        }

        $this->services[$offset] = $value;

        return $this;
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

    /**
     * @param string $offset
     * @param callable $callback
     * @throws \InvalidArgumentException
     * @return unknown
     */
    public function extend($offset, $callback)
    {
        if (isset($this->resolved[$offset])) {
            throw new \InvalidArgumentException(sprintf('[' . __METHOD__ . '] Service "%s" is resolved!', $offset), 500);
        }

        if (!is_object($callback) || !method_exists($callback, '__invoke')) {
            throw new \InvalidArgumentException('[' . __METHOD__ . '] Provided callback must be \Closure or implements __invoke!', 500);
        }

        $service = $this->services[$offset];

        $extended = function ($c) use ($service, $callback) {
            return $callback($service($c), $c);
        };

        return $this[$offset] = $extended;
    }

    /**
     * @param string $alias
     * @param string $service
     * @return \Micro\Container\Container
     */
    public function alias($alias, $service)
    {
        $this->aliases[$alias] = $service;

        return $this;
    }

    /**
     * @param sring $alias
     * @throws \Exception
     * @return string
     */
    public function resolveAlias($alias)
    {
        $stack = [];

        while (isset($this->aliases[$alias])) {

            if (isset($stack[$alias])) {
                throw new \Exception(sprintf(
                    'Circular alias reference: %s -> %s',
                    implode(' -> ', $stack),
                    $alias
                ));
            }

            $stack[$alias] = $alias;

            $alias = $this->aliases[$alias];
        }

        return $alias;
    }
}