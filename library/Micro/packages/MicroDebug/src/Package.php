<?php

namespace MicroDebug;

use Micro\Application\Package as BasePackage;

class Package extends BasePackage
{
    public function boot()
    {
        if (!config('debug.enabled', 0)) {
            return;
        }

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