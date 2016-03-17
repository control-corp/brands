<?php

namespace Nomenclatures\Model;

use Micro\Model\DatabaseAbstract;

class Currencies extends DatabaseAbstract
{
    protected $table = Table\Currencies::class;
    protected $entity = Entity\Currency::class;
}