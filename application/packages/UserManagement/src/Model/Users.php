<?php

namespace UserManagement\Model;

use Micro\Database\Model\ModelAbstract;

class Users extends ModelAbstract
{
    protected $_name = 'users';

    protected $_rowClass = Entity\User::class;

    public function login($username, $password)
    {
        $select = $this->select(true)
                       ->setIntegrityCheck(false)
                       ->joinInner('groups', 'users.group_id = groups.id', array('groups.alias as group'));

        $select->where('username = ?', $username);

        if ($password !== \null) {
            $select->where('password = ?', $password);
        }

        return $this->fetchRow($select);
    }
}