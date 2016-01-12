<?php

namespace App;

use Micro\Application\Controller;
use Micro\Auth\Auth;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Security;
use UserManagement\Model\Users;

class Index extends Controller
{
    public function index()
    {

    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $password = $this->request->getPost('password');
            if ($password) {
                $usersModel = new Users();
                $usersModel->update(array(
                    'password' => Security::hash($password),
                ), array('id = ?' => identity()->id));
                return new RedirectResponse(route('profile'));
            }
        }
    }

    public function register()
    {
        if ($this->request->isPost()) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            if ($username && $password) {
                $usersModel = new Users();
                $usersModel->insert(array(
                    'username' => $username,
                    'password' => Security::hash($password),
                    'group_id' => 2
                ));
                return new RedirectResponse(route('login'));
            }
        }
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $usersModel = new Users();
            $user = $usersModel->login($username, \null);
            if ($user !== \null && Security::verity($password, $user['password'])) {
                Auth::getInstance()->setIdentity($user);
                return new RedirectResponse(route('home'));
            }
        }
    }

    public function logout()
    {
        Auth::getInstance()->clearIdentity();

        return new RedirectResponse(route('home'));
    }
}

