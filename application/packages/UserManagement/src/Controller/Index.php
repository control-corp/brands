<?php

namespace UserManagement\Controller;

use Micro\Application\Controller;
use Micro\Auth\Auth;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Security;
use Micro\Form\Form;
use UserManagement\Model\Users;

class Index extends Controller
{
    public function profile()
    {
        if (!identity()) {
            return new RedirectResponse(route('login'));
        }

        if ($this->request->isPost()) {

            $password = $this->request->getPost('password');

            if ($password) {
                $usersModel = new Users();
                $user = $usersModel->find(identity()->getId());
                if ($user) {
                    $user->password = Security::hash($password);
                    $usersModel->save($user);
                }
            }

            $redirect = new RedirectResponse(route());

            return $redirect->withFlash();
        }
    }

    public function register()
    {
        $form = new Form(package_path('UserManagement', 'forms/register.php'));

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                $user = $usersModel->createEntity()->setFromArray(array(
                    'username' => $data['username'],
                    'password' => Security::hash($data['password']),
                    'group_id' => 2
                ));
                $usersModel->save($user);
                return (new RedirectResponse(route('login')))->withFlash('Успешно се регистрирахте');
            }
        }

        return ['form' => $form];
    }

    public function login()
    {
        $form = new Form(package_path('UserManagement', 'forms/login.php'));

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                if ($usersModel->login($data['username'], $data['password'])) {
                    return new RedirectResponse(route('home'));
                } else {
                    $form->password->addError('Невалидни данни');
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