<?php

namespace App\Bridge;

use Micro\Exception\ExceptionHandlerInterface;
use Whoops\Run as Runner;

class Whoops implements ExceptionHandlerInterface
{
    protected $whoops;

    public function __construct(Runner $whoops)
    {
        $this->whoops = $whoops;
    }

    public function handleException(\Exception $e)
    {
        return $this->whoops->handleException($e);
    }
}