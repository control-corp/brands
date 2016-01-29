<?php

namespace UserManagement\Model\Entity;

use Micro\Model\EntityAbstract;

class Group extends EntityAbstract
{
    protected $id;
    protected $parentId;
    protected $name;
    protected $alias;
    protected $rights = [];
    protected $disabled = 0;
}