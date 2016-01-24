<?php

namespace UserManagement\Model;

use Micro\Database\Model\ModelAbstract;

class Users extends ModelAbstract
{
    protected $_name = 'users';

    public function login($username, $password)
    {
        $select = $this->getUserSelect();

        $select->where('username = ?', $username);

        if ($password !== \null) {
            $select->where('password = ?', $password);
        }

        return $this->fetchRow($select);
    }

    public function findUser($id)
    {
        $select = $this->getUserSelect();

        $select->where('users.id = ?', (int) $id);

        return $this->fetchRow($select);
    }

    protected function getUserSelect()
    {
        $select = $this->select(\true)
                       ->setIntegrityCheck(\false)
                       ->joinInner('groups', 'users.group_id = groups.id', array('groups.alias as group'));

        return $select;
    }
}