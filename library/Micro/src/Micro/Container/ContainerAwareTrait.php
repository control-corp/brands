<?php

namespace Micro\Container;

trait ContainerAwareTrait
{
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }
}