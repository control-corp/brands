<?php

namespace Micro\Auth;

class Auth
{
    /**
     * @var \Micro\Auth\Auth
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $namespace = 'default';

    /**
     * @var \Micro\Auth\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var callable
     */
    protected static $resolver;

    protected function __construct() {}

    protected function __clone() {}

    public function getStorage()
    {
        if ($this->storage === \null) {
            $this->storage = new Storage\Session($this->getNamespace());
        }

        return $this->storage;
    }

    /**
     * @param \Micro\Auth\Storage\StorageInterface $storage
     */
    public function setStorage(Storage\StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return \Micro\Auth\Auth
     */
    public static function getInstance()
    {
        if (static::$instance === \null) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return \Micro\Auth\Auth
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getStorage()->read();
    }

    /**
     * @param mixed $data
     * @return \Micro\Auth\Auth
     */
    public function setIdentity($data)
    {
        $this->getStorage()->write($data);

        return $this;
    }

    /**
     * @return \Micro\Auth\Auth
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();

        return $this;
    }

    /**
     * @param boolean $force
     * @return mixed
     */
    public static function identity($force = false)
    {
        static $cache = false;

        if ($force === false && $cache !== false) {
            return $cache;
        }

        $identity = static::getInstance()->getIdentity();

        if ($identity === null || static::$resolver === null) {
            return null;
        }

        return $cache = call_user_func(static::$resolver, $identity);
    }

    /**
     * @param callable $resolver
     */
    public static function setResolver($resolver)
    {
        static::$resolver = $resolver;
    }
}