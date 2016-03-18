<?php

namespace Nomenclatures\Model;

use Micro\Model\DatabaseAbstract;

class DesignStatuses extends DatabaseAbstract
{
    protected $table = Table\DesignStatuses::class;

    protected $entity = Entity\DesignStatus::class;
}