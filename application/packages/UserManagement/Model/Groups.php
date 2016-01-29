<?php

namespace UserManagement\Model;

use Micro\Model\DatabaseAbstract;

class Groups extends DatabaseAbstract
{
    protected $table = Table\Groups::class;

    protected $entity = Entity\Group::class;
}