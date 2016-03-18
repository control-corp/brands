<?php

namespace UserManagement\Controller\Admin;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Auth\Auth;
use Micro\Http\Response\RedirectResponse;
use Micro\Application\Security;
use Micro\Form\Form;
use Micro\Grid\Grid;
use UserManagement\Model\Users;
use Micro\Model\EntityInterface;
use Micro\Application\Utils;

class Index extends Controller
{
    protected $scope = 'admin';

    public function indexAction()
    {
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnAdd'])) {
                return new RedirectResponse(route(\null, ['action' => 'add', 'id' => \null, 'page' => \null]));
            }
        }

        $model = new Users();

        $grid = new Grid(
            $model,
            package_path('UserManagement', '/Resources/grids/admin/index.php')
        );

        $grid->getRenderer()->setView($this->view);

        return new View('admin/index/index', ['grid' => $grid]);
    }

    public function addAction(EntityInterface $entity = \null)
    {
        $model = new Users();

        if ($entity === \null) {
            $entity = $model->createEntity();
        }

        $form = new Form(package_path('UserManagement', '/Resources/forms/admin/users-add.php'));

        $form->populate($entity->toArray());

        if ($entity->getId()) {
            $form->password->setRequired(false);
            $form->repassword->setRequired(false);
        }

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            if (isset($post['btnBack'])) {
                return new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));
            }

            $post = Utils::arrayMapRecursive('trim', $post);

            $form->isValid($post);

            if (!$form->hasErrors()) {

                $post = Utils::arrayMapRecursive('trim', $post, true);

                if (array_key_exists('password', $post)) {
                    if ($post['password']) {
                        $post['password'] = Security::hash($post['password']);
                    } else {
                        unset($post['password']);
                    }
                }

                $entity->setFromArray($post);

                try {
                    $model->save($entity);

                    if (isset($post['btnApply'])) {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'edit', 'id' => $entity[$model->getIdentifier()]]));
                    } else {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));
                    }

                    return $redirectResponse->withFlash('Информацията е записана');

                } catch (\Exception $e) {

                    if ($entity->getId()) {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'edit', 'id' => $entity->getId()]));
                    } else {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'add', 'id' => \null]));
                    }

                    return $redirectResponse->withFlash((env('development') ? $e->getMessage() : 'Възникна грешка. Опитайте по-късно'), 'danger');
                }
            }
        }

        return new View('admin/index/add', ['form' => $form, 'item' => $entity]);
    }

    public function editAction()
    {
        $model = new Users();

        $entity = $model->find((int) $this->request->getParam('id', 0));

        if ($entity === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        return $this->addAction($entity);
    }

    public function profileAction()
    {
        $form = new Form(package_path('UserManagement', 'Resources/forms/admin/profile.php'));

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

        return new View('admin/index/profile', ['form' => $form]);
    }

    public function loginAction()
    {
        if (identity()) {
            return new RedirectResponse(route('admin', [], \true));
        }

        $form = new Form(package_path('UserManagement', 'Resources/forms/admin/login.php'));

        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            if ($form->isValid($data)) {
                $usersModel = new Users();
                if ($usersModel->login($data['username'], $data['password'])) {
                    if (($backTo = $this->request->getParam('backTo')) !== \null) {
                        return new RedirectResponse(urldecode($backTo));
                    } else {
                        return new RedirectResponse(route('admin', [], \true));
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

        return new RedirectResponse(route('admin', [], \true));
    }
}