<?php

namespace Micro\Application\Controller;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\RedirectResponse;
use Micro\Database\Table\Row;
use Micro\Form\Form;
use Micro\Application\Utils;
use Micro\Database\Model\ModelAbstract;

class Crud extends Controller
{
    protected $model;

    /**
     * @throws \Exception
     * @return \Micro\Database\Model\ModelAbstract
     */
    public function getModel()
    {
        if ($this->model === \null) {
            $package = $this->request->getParam('package');
            $controller = $this->request->getParam('controller');
            if ($package && $controller) {
                $model = ucfirst(Utils::camelize($package)) . '\Model\\' . ucfirst(Utils::camelize($controller));
                if (class_exists($model, \true)) {
                    $this->model = new $model;
                }
            }
        } else if (is_string($this->model) && class_exists($this->model, \true)) {
            $this->model = new $this->model;
        }

        if (!is_object($this->model) || !$this->model instanceof ModelAbstract) {
            throw new \Exception(
                sprintf(
                    'Model [%s] must be instanceof Micro\\Database\\Model\\ModelAbstract',
                    (is_object($this->model) ? get_class($this->model) : gettype($this->model))
                )
            );
        }

        return $this->model;
    }

    public function index()
    {
        $controller = $this->request->getParam('controller');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnAdd'])) {
                return new RedirectResponse(route(\null, ['action' => 'add', 'page' => \null]));
            }
        }

        return new View(
            $controller . '/index',
            ['items' => $this->getModel()->getItems()]
        );
    }

    public function add(Row $entity = \null)
    {
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        $form = new Form(package_path(ucfirst(Utils::camelize($package)), 'forms/' . $controller . '-add.php'));

        if ($entity !== \null) {
            $form->populate($entity->toArray());
        }

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            if ($form->isValid($post)) {

                if ($entity === \null) {
                    $this->getModel()->save($post + ['language_id' => 2]);
                } else {
                    $entity->setFromArray($post);
                    $this->getModel()->save($entity);
                }

                $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index'], \false, \true));

                return $redirectResponse->withFlash(sprintf('Записът е %s', ($entity ? 'редактиран' : 'добавен')));
            }
        }

        return new View(
            $controller . '/add',
            ['form' => $form, 'item' => $entity]
        );
    }

    public function edit()
    {
        $item = $this->getModel()->getItem((int) $this->request->getParam('id', 0));

        if ($item === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        return $this->add($item);
    }

    public function delete()
    {
        $id = (int) $this->request->getParam('id', 0);
        $ids = $this->request->getParam('ids', []);

        if ($id) {
            $ids = array($id);
        }

        $ids = array_filter($ids);

        if (!empty($ids)) {
            $this->getModel()->delete(array('id IN (?)' => array_map('intval', $ids)));
        }

        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null, 'ids' => \null], \false, \true));

        return $redirectResponse->withFlash('Записът е изтрит');
    }

    public function view()
    {
        $item = $this->getModel()->getItem((int) $this->request->getParam('id', 0));

        if ($item === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        $controller = $this->request->getParam('controller');

        return new View($controller . '/view', ['item' => $item]);
    }
}