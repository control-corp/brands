<?php

namespace MicroDebug;

use Micro\Application\Package as BasePackage;

class Package extends BasePackage
{
    public function boot()
    {
        $handlers = [
            new Handler\FirePHP,
            new Handler\Performance,
            new Handler\DevTools,
        ];

        foreach ($handlers as $handler) {
            $handler->boot();
        }
    }
}