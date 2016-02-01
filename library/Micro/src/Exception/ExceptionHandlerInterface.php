<?php

namespace Micro\Exception;

interface ExceptionHandlerInterface
{
    public function handleException(\Exception $e);
}