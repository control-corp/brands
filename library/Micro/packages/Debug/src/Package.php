<?php

namespace Debug;

use Micro\Application\Package as BasePackage;

class Package extends BasePackage
{
    public function boot()
    {
        if (!config('debug.enabled', 0)) {
            return;
        }

        $handlers = [
            new Handler\FirePHP($this->container),
            new Handler\Performance($this->container),
            new Handler\DevTools($this->container),
        ];

        foreach ($handlers as $handler) {
            $handler->boot();
        }
    }
}