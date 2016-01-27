<?php

namespace UserManagement\Model\Entity;

use Micro\Model\EntityAbstract;
use Micro\Acl\RoleInterface;
use Micro\Auth\Identity;

class User extends EntityAbstract implements RoleInterface, Identity
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

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getEmail()
    {
        return \null;
    }

    public function getGroups()
    {
        return [$this->group_id];
    }

    public function isActive()
    {
        return \true;
    }
}