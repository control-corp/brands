<?php

namespace Micro\Application\Controller;

use Micro\Application\Controller;
use Micro\Http\Response\RedirectResponse;
use Micro\Form\Form;
use Micro\Grid;
use Micro\Application\Utils;
use Micro\Model\EntityInterface;
use Micro\Http\Response;
use Micro\Translator\Language\LanguageInterface;
use Micro\Model\ModelInterface;

class Crud extends Controller
{
    protected $ipp = 10;

    /**
     * @var \Micro\Model\ModelInterface
     */
    protected $model;

    /**
     * @throws \Exception
     * @return \Micro\Model\ModelInterface
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

        if (!$this->model instanceof ModelInterface) {
            throw new \Exception(
                sprintf(
                    'Model [%s] must be instanceof %s',
                    (is_object($this->model) ? get_class($this->model) : gettype($this->model)),
                    ModelInterface::class
                )
            );
        }

        return $this->model;
    }

    /**
     * @return \Micro\Http\Response\RedirectResponse|\Micro\Application\View
     */
    public function indexAction()
    {
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnAdd'])) {
                return new RedirectResponse(route(\null, ['action' => 'add', 'id' => \null, 'page' => \null]));
            }
        }

        $filters = $this->handleFilters();

        if ($filters instanceof Response) {
            return $filters;
        }

        $model = $this->getModel();

        if (($language = app('language')) instanceof LanguageInterface) {
            $model->addJoinCondition('language_id', $language->getId());
        }

        $ipp = max($this->ipp, $this->request->getParam('ipp', $this->ipp));
        $page = max(1, $this->request->getParam('page', 1));
        $orderField = $this->request->getParam('orderField', $model->getIdentifier());
        $orderDir = strtoupper($this->request->getParam('orderDir', 'desc'));

        $model->addOrder($orderField, $orderDir);

        $model->addFilters($filters);

        $this->modifyModel($filters);

        $grid = new Grid\Grid(
            $model,
            package_path(ucfirst(Utils::camelize($package)), '/Resources/grids/' . $controller . '.php')
        );

        $grid->getRenderer()->setView($this->view);

        $column = $grid->getColumn($orderField);

        if ($column instanceof Grid\Column) {
            $column->setSorted($orderDir);
        }

        $grid->setIpp($ipp);
        $grid->setPageNumber($page);

        $this->view->setTemplate($controller . '/index');

        $this->view->addData(['grid' => $grid, 'filters' => $filters]);

        return $this->view;
    }

    /**
     * @param array $filters
     */
    protected function modifyModel(array $filters)
    {
    }

    /**
     * @param EntityInterface $entity
     * @return \Micro\Http\Response\RedirectResponse|\Micro\Application\View
     */
    public function addAction(EntityInterface $entity = \null)
    {
        $package = $this->request->getParam('package');
        $controller = $this->request->getParam('controller');

        $model = $this->getModel();

        if ($entity === \null) {
            $entity = $model->createEntity();
        }

        $form = new Form(package_path(ucfirst(Utils::camelize($package)), '/Resources/forms/' . $controller . '-add.php'));

        $form->populate($entity->toArray());

        $this->prepareForm($form, $entity);

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            if (isset($post['btnBack'])) {
                return new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));
            }

            $post = Utils::arrayMapRecursive('trim', $post);

            $this->modifyPost($form, $entity, $post);

            $this->preValidate($form, $entity, $post);

            $form->isValid($post);

            $this->postValidate($form, $entity, $post);

            if (!$form->hasErrors()) {

                if (!isset($post['language_id']) && ($language = app('language')) instanceof LanguageInterface) {
                    $post['language_id'] = $language->getId();
                }

                try {

                    $post = Utils::arrayMapRecursive('trim', $post, true);

                    $this->modifyData($post);

                    $entity->setFromArray($post);

                    $this->modifyEntity($entity);

                    $model->save($entity);

                    if (isset($post['btnApply'])) {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'edit', 'id' => $entity[$model->getIdentifier()]]));
                    } else {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));
                    }

                    return $redirectResponse->withFlash('Информацията е записана');

                } catch (\Exception $e) {

                    if ($entity[$model->getIdentifier()]) {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'edit', 'id' => $entity[$model->getIdentifier()]]));
                    } else {
                        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'add', 'id' => \null]));
                    }

                    return $redirectResponse->withFlash($e->getMessage(), 'danger');
                }
            }
        }

        $this->view->addData(['form' => $form, 'item' => $entity]);

        $this->view->setTemplate($controller . '/add');

        return $this->view;
    }

    /**
     * @param Form $form
     * @param EntityInterface $entity
     */
    protected function prepareForm(Form $form, EntityInterface $entity)
    {
    }

    /**
     * @param Form $form
     * @param EntityInterface $entity
     * @param array $data
     */
    protected function modifyPost(Form $form, EntityInterface $item, array &$data)
    {
    }

    /**
     * @param Form $form
     * @param EntityInterface $entity
     * @param array $data
     */
    protected function preValidate(Form $form, EntityInterface $item, array $data)
    {

    }

    /**
     * @param Form $form
     * @param EntityInterface $entity
     * @param array $data
     */
    protected function postValidate(Form $form, EntityInterface $item, array $data)
    {
    }

    /**
     * @param array $data
     */
    protected function modifyData(array &$data)
    {
    }

    /**
     * @param EntityInterface $entity
     */
    protected function modifyEntity(EntityInterface $entity)
    {
    }

    /**
     * @throws \Exception
     * @return \Micro\Http\Response\RedirectResponse|\Micro\Application\View
     */
    public function editAction()
    {
        $model = $this->getModel();

        if (($language = app('language')) instanceof LanguageInterface) {
            $model->addJoinCondition('language_id', $language->getId());
        }

        $entity = $model->find((int) $this->request->getParam('id', 0));

        if ($entity === \null) {
            throw new \Exception(sprintf('Записът не е намерен'), 404);
        }

        return $this->addAction($entity);
    }

    /**
     * @return \Micro\Http\Response\RedirectResponse
     */
    public function deleteAction()
    {
        $id = (int) $this->request->getParam('id', 0);
        $ids = $this->request->getParam('ids', []);

        if ($id) {
            $ids = [$id];
        }

        $ids = array_filter($ids);

        $affected = 0;

        if (!empty($ids)) {
            $this->getModel()->addFilters(['id' => $ids]);
            $items = $this->getModel()->getItems();
            foreach ($items as $item) {
                try {
                    $affected += $this->getModel()->delete($item);
                } catch (\Exception $e) {

                }
            }
        }

        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null, 'ids' => \null]));

        return $redirectResponse->withFlash(sprintf('Информацията е записана. Бяха изтрити %d запис(а)', $affected));
    }

    /**
     * Activate item Action
     * @param number $active
     */
    public function activateAction($active = 1)
    {
        $id = (int) $this->request->getParam('id');
        $ids = $this->request->getParam('ids', []);

        if ($id) {
            $ids = array($id);
        }

        $ids = array_filter($ids);

        $affected = 0;

        if (!empty($ids)) {
            $this->getModel()->addWhere('id', $ids);
            $items = $this->getModel()->getItems();
            foreach ($items as $item) {
                try {
                    $affected += $this->getModel()->activate($item, $active);
                } catch (\Exception $e) {

                }
            }
        }

        $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null, 'ids' => \null]));

        return $redirectResponse->withFlash(sprintf('Информацията е записана. Бяха %s %d запис(а)', ($active ? 'активирани' : 'деактивирани'), $affected));
    }

    /**
     * Deactivate item Action
     */
    public function deactivateAction()
    {
        return $this->activateAction(0);
    }

    /**
     * Handle filters
     * @param string $key
     * @return \Micro\Http\Response\RedirectResponse|array
     */
    protected function handleFilters($key = 'filters')
    {
        $filters = $this->request->getParam($key);

        if ($this->request->isPost()) {

            $post = $this->request->getPost($key, []);

            if (isset($post['reset'])) {
                return new RedirectResponse(route(\null, [$key => \null, 'id' => \null, 'page' => \null, 'orderDir' => \null, 'orderField' => \null]));
            }

            if (isset($post['filter'])) {
                unset($post['filter']);
                foreach ($post as $k => $v) {
                    if (\trim((string) $v) === '') {
                        unset($post[$k]);
                    }
                }
                if (!empty($post)) {
                    return new RedirectResponse(route(\null, [$key => Utils::base64urlEncode(http_build_query($post)), 'id' => \null, 'page' => \null, 'orderDir' => \null, 'orderField' => \null]));
                } else {
                    return new RedirectResponse(route(\null, [$key => \null, 'id' => \null, 'page' => \null, 'orderDir' => \null, 'orderField' => \null]));
                }
            }
        }

        if ($filters) {
            parse_str(Utils::base64urlDecode($filters, \true), $filters);
            if (empty($filters)) {
                $filters = [];
            }
        } else {
            $filters = [];
        }

        return $filters;
    }
}