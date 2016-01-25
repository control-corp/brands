<?php

namespace Nomenclatures\Model;

use Micro\Model\ModelAbstract;

class Cities extends ModelAbstract
{
    protected $table = Table\Cities::class;

    protected $entity = Entity\City::class;
}