<?php

namespace Micro\Application\Controller;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\RedirectResponse;
use Micro\Form\Form;
use Micro\Grid;
use Micro\Application\Utils;
use Micro\Model\ModelAbstract;
use Micro\Model\EntityAbstract;

class Crud extends Controller
{
    protected $ipp = 10;

    protected $model;

    /**
     * @throws \Exception
     * @return \Micro\Model\ModelAbstract
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
                    'Model [%s] must be instanceof %s',
                    (is_object($this->model) ? get_class($this->model) : gettype($this->model)),
                    ModelAbstract::class
                )
            );
        }

        return $this->model;
    }

    public function index()
    {
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnAdd'])) {
                return new RedirectResponse(route(\null, ['action' => 'add', 'page' => \null]));
            }
        }

        $model = $this->getModel();

        $model->addJoinCondition('language_id', app('language'));

        $ipp = max($this->ipp, $this->request->getParam('ipp', $this->ipp));
        $page = max(1, $this->request->getParam('page', 1));
        $orderField = $this->request->getParam('orderField', current($model->getTable()->info('primary')));
        $orderDir = strtoupper($this->request->getParam('orderDir', 'desc'));

        $model->addOrder($orderField, $orderDir);

        $grid = new Grid\Grid(
            $model,
            package_path(ucfirst(Utils::camelize($package)), 'grids/' . $controller . '.php')
        );

        $column = $grid->getColumn($orderField);

        if ($column instanceof Grid\Column) {
            $column->setSorted($orderDir);
        }

        $grid->setIpp($ipp);
        $grid->setPageNumber($page);

        return new View(
            $controller . '/index',
            ['grid' => $grid]
        );
    }

    public function add(EntityAbstract $entity = \null)
    {
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        $form = new Form(package_path(ucfirst(Utils::camelize($package)), 'forms/' . $controller . '-add.php'));

        $model = $this->getModel();

        if ($entity !== \null) {
            $form->populate($entity->toArray());
        } else {
            $entity = $model->createEntity();
        }

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            if ($form->isValid($post)) {

                if (\null !== ($table = $model->getTableByColumn('language_id'))) {
                    if (!isset($post['language_id'])) {
                        $post['language_id'] = app('language');
                    }
                }

                $model->save($entity->setFromArray($post));

                $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index'], \false, \true));

                return $redirectResponse->withFlash(sprintf('Информацията е записана'));
            }
        }

        return new View(
            $controller . '/add',
            ['form' => $form, 'item' => $entity]
        );
    }

    public function edit()
    {
        $model = $this->getModel();

        $model->addJoinCondition('language_id', app('language'));

        $item = $model->find((int) $this->request->getParam('id', 0));

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
            $ids = [$id];
        }

        $ids = array_filter($ids);

        if (!empty($ids)) {
            $this->getModel()->getTable()->delete(['id IN (?)' => array_map('intval', $ids)]);
        }

        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null, 'ids' => \null], \false, \true));

        return $redirectResponse->withFlash('Информацията е записана');
    }

    public function view()
    {
        $item = $this->getModel()->find((int) $this->request->getParam('id', 0));

        if ($item === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        $controller = $this->request->getParam('controller');

        return new View($controller . '/view', ['item' => $item]);
    }
}