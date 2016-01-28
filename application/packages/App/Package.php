<?php

namespace App;

use Micro\Application\Package as BasePackage;
use Micro\Database\Table\TableAbstract;

class Package extends BasePackage
{
    public function boot()
    {
        TableAbstract::setDefaultAdapter($this->container['db']);

        TableAbstract::setDefaultMetadataCache($this->container['cache']);
    }
}