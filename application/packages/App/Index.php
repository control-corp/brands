<?php

namespace App;

use Micro\Application\Controller;
use Micro\Auth\Auth;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Security;
use Micro\Form\Form;
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
        $form = new Form(__DIR__ . '/resources/forms/register.php');

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                $usersModel->insert(array(
                    'username' => $data['username'],
                    'password' => Security::hash($data['password']),
                    'group_id' => 2
                ));
                return new RedirectResponse(route('login'));
            }
        }

        return ['form' => $form];
    }

    public function login()
    {
        $form = new Form(__DIR__ . '/resources/forms/login.php');

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                $user = $usersModel->login($data['username'], \null);
                if ($user !== \null && Security::verity($data['password'], $user['password'])) {
                    Auth::getInstance()->setIdentity($user);
                    return new RedirectResponse(route('home'));
                }
            }
        }

        return ['form' => $form];
    }

    public function logout()
    {
        Auth::getInstance()->clearIdentity();

        return new RedirectResponse(route('home'));
    }
}

