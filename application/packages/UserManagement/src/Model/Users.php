<?php

namespace UserManagement\Model;

use Micro\Model\ModelAbstract;
use Micro\Application\Security;
use Micro\Auth\Auth;

class Users extends ModelAbstract
{
    protected $table = Table\Users::class;

    protected $entity = Entity\User::class;

    public function login($username, $password)
    {
        $this->addWhere('username', $username);

        $user = $this->getItem();

        if ($user !== \null && Security::verity($password, $user['password'])) {
            Auth::getInstance()->setIdentity($user['id']);
            return \true;
        }

        return \false;
    }
}