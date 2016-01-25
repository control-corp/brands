<?php

namespace UserManagement\Model\Entity;

use Micro\Model\EntityAbstract;
use Micro\Acl\RoleInterface;

class User extends EntityAbstract implements RoleInterface
{
    protected $id;
    protected $group_id;
    protected $username;
    protected $password;

    public function getRoleId()
    {
        static $roleId;

        if ($roleId === \null) {
            $roleId = app('db')->fetchOne('SELECT alias FROM groups WHERE id = ?', array((int) $this->group_id));
        }

        return $roleId;
    }
}