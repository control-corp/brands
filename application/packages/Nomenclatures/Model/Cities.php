<?php

namespace Nomenclatures\Model;

use Micro\Model\DatabaseAbstract;

class Cities extends DatabaseAbstract
{
    protected $table = Table\Cities::class;

    protected $entity = Entity\City::class;
}