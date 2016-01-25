<?php

namespace Nomenclatures\Model;

use Micro\Model\ModelAbstract;

class Countries extends ModelAbstract
{
    protected $table = Table\Countries::class;

    protected $entity = Entity\Country::class;
}