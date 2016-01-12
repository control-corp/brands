<?php

namespace UserManagement\Model;

use Micro\Database\Table\TableAbstract;

class Users extends TableAbstract
{
    protected $_name = 'users';

    protected $_rowClass = 'UserManagement\Model\Entity\User';

    public function __construct($config = array())
    {
        static::setDefaultAdapter(app('db'));

        static::setDefaultMetadataCache(app('cache'));

        parent::__construct($config);
    }

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