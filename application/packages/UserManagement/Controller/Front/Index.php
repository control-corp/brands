<?php

namespace UserManagement\Controller\Front;

use Micro\Application\Controller;
use Micro\Auth\Auth;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Security;
use Micro\Form\Form;
use UserManagement\Model\Users;

class Index extends Controller
{
    public function profileAction()
    {
        $form = new Form(package_path('UserManagement', 'Resources/forms/profile.php'));

        $form->username->setValue(identity()->getUsername());

        if ($this->request->isPost()) {

            $data = $this->request->getPost();

            if (isset($data['btnBack'])) {
                return new RedirectResponse(route());
            }

            if ($form->isValid($data)) {

                $usersModel = new Users();
                $user = $usersModel->find(identity()->getId());

                if ($user && $data['password']) {
                    $user->password = Security::hash($data['password']);
                    $usersModel->save($user);
                }

                $redirect = new RedirectResponse(route());

                return $redirect->withFlash();
            }
        }

        return ['form' => $form];
    }

    public function loginAction()
    {
        $form = new Form(package_path('UserManagement', 'Resources/forms/login.php'));

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                if ($usersModel->login($data['username'], $data['password'])) {
                    if (($backTo = $this->request->getParam('backTo')) !== \null) {
                        return new RedirectResponse(urldecode($backTo));
                    } else {
                        return new RedirectResponse(route('home'));
                    }
                } else {
                    $form->password->addError('Невалидни данни');
                }
            }
        }

        return ['form' => $form];
    }

    public function logoutAction()
    {
        Auth::getInstance()->clearIdentity();

        return new RedirectResponse(route('home'));
    }
}