<?php

namespace UserManagement\Model\Entity;

use Micro\Database\Table\Row;

class User extends Row
{
    public function getId()
    {
        return $this->id;
    }

    public function getGroup()
    {
        return $this->group;
    }
}