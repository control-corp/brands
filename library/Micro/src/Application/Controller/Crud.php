<?php

namespace Micro\Application\Controller;

use Micro\Application\Controller;
use Micro\Application\View;
use Micro\Http\Response\RedirectResponse;
use Micro\Database\Table\Row;
use Micro\Form\Form;
use Micro\Grid;
use Micro\Application\Utils;
use Micro\Database\Model\ModelAbstract;

class Crud extends Controller
{
    protected $ipp = 10;

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
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnAdd'])) {
                return new RedirectResponse(route(\null, ['action' => 'add', 'page' => \null]));
            }
        }

        $defaultOrderField = current($this->getModel()->info('primary'));
        $defaultOrderDir = 'desc';

        $orderField = $this->request->getParam('orderField', $defaultOrderField);
        $orderDir = strtolower($this->request->getParam('orderDir', $defaultOrderDir));

        $columns = $this->getModel()->getColumns();

        if (!isset($columns[$orderField])) {
            $orderField = $defaultOrderField;
        }

        if ($orderDir !== 'asc' && $orderDir !== 'desc') {
            $orderDir = $defaultOrderDir;
        }

        if (isset($columns['language_id'])) {
            $this->getModel()->setDependentWhere(['language_id' => app('language')]);
        }

        $modelSelect = $this->getModel()->buildSelect(\null, $orderField . ' ' . $orderDir);

        $grid = new Grid\Grid(
            $modelSelect,
            package_path(ucfirst(Utils::camelize($package)), 'grids/' . $controller . '.php')
        );

        $column = $grid->getColumn($orderField);

        if ($column instanceof Grid\Column) {
            $column->setSorted($orderDir);
        }

        $grid->setIpp(max($this->ipp, $this->request->getParam('ipp', $this->ipp)));
        $grid->setPageNumber(max(1, $this->request->getParam('page', 1)));

        return new View(
            $controller . '/index',
            ['grid' => $grid]
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

                    $columns = $this->getModel()->getColumns();

                    if (isset($columns['language_id'])) {
                        $post += ['language_id' => app('language')];
                    }

                    $this->getModel()->save($post);

                } else {
                    $this->getModel()->save(
                        $entity->setFromArray($post)
                    );
                }

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
        $columns = $this->getModel()->getColumns();

        if (isset($columns['language_id'])) {
            $this->getModel()->setDependentWhere(['language_id' => app('language')]);
        }

        $item = $this->getModel()->getEntity((int) $this->request->getParam('id', 0));

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

        return $redirectResponse->withFlash('Информацията е записана');
    }

    public function view()
    {
        $columns = $this->getModel()->getColumns();

        if (isset($columns['language_id'])) {
            $this->getModel()->setDependentWhere(['language_id' => app('language')]);
        }

        $item = $this->getModel()->getEntity((int) $this->request->getParam('id', 0));

        if ($item === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        $controller = $this->request->getParam('controller');

        return new View($controller . '/view', ['item' => $item]);
    }
}