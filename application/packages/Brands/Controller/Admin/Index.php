<?php

namespace Brands\Controller\Admin;

use Micro\Application\Controller\Crud;
use Micro\Application\Utils;
use Micro\Http\Response;
use Micro\Model\EntityInterface;
use Micro\Form\Form;
use Micro\Database\Expr;
use Micro\Http\Response\RedirectResponse;
use Nomenclatures\Model\Countries;

class Index extends Crud
{
    protected $ipp = 30;

    protected $model = \Brands\Model\Brands::class;

    protected $scope = 'admin';

    /**
     * (non-PHPdoc)
     * @see \Micro\Application\Controller::init()
     */
    public function init()
    {
        parent::init();

        $nomStatuses = new \Nomenclatures\Model\Statuses();
        $this->view->assign('nomStatuses', $nomStatuses->fetchCachedPairs());

        $nomTypes = new \Nomenclatures\Model\Types();
        $this->view->assign('nomTypes', $nomTypes->fetchCachedPairs());

        $nomNotifiers = new \Nomenclatures\Model\Notifiers();
        $this->view->assign('nomNotifiers', $nomNotifiers->fetchCachedPairs());

        $nomClasses = new \Nomenclatures\Model\BrandClasses();
        $this->view->assign('nomClasses', $nomClasses->fetchCachedPairs());
        $this->view->assign('nomClassesCodes', $nomClasses->fetchCachedPairs(null, array('id', 'code')));
    }

    public function indexAction()
    {
        if (($response = parent::indexAction()) instanceof Response) {
            return $response;
        }

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['btnWizzard'])) {
                return new RedirectResponse(route(\null, ['action' => 'wizzard', 'id' => \null, 'page' => \null]));
            }
        }

        $form = new Form(package_path('Brands', 'Resources/forms/admin/index-filters.php'));

        $form->populate($this->view->filters);

        $this->view->assign('form', $form);
    }

    protected function modifyModel(array $filters)
    {
        $now = date('Y-m-d');

        $this->getModel()->addWhere(new Expr('reNewDate IS NULL OR reNewDate >= DATE("' . $now . '")'));

        if ($this->request->getParam('orderField') === null) {

            $model = $this->getModel();

            $model->addOrder(new Expr('IF(reNewDate IS NOT NULL AND (TIMESTAMPDIFF(MONTH, "' . $now . '", reNewDate) + 1) <= 3, 1, 0) DESC'));

            $model->addOrder(new Expr('reNewDate ASC'));
        }

        if (isset($filters['months']) && $filters['months']) {
            $months = (int) $filters['months'];
            if ($months) {
                $this->getModel()->addWhere(new Expr('
                    reNewDate IS NOT NULL
                    AND reNewDate >= "' . $now . '"
                    AND TIMESTAMPDIFF(MONTH, "' . $now . '", reNewDate) + 1 <= ' . $months . '
                '));
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Micro\Application\Controller\Crud::postValidate()
     */
    protected function postValidate(Form $form, EntityInterface $item, array $data)
    {
        if (isset($data['name']) && $data['name'] && isset($data['countryId']) && $data['countryId']) {
            $m = new \Brands\Model\Table\Brands();
            $where = array('name = ?' => $data['name'], 'countryId = ?' => $data['countryId'], 'typeId = ?' => $data['typeId']);
            if ($item->getId()) {
                $where['id <> ?'] = $item->getId();
            }
            if ($m->fetchRow($where)) {
                $form->countryId->addError('Тази марка и тип съществува за тази държава');
                $form->markAsError();
            }
        }

        if ($data['statusId'] && !$data['statusDate']) {
            $form->statusDate->addError('Дата на статуса е задължителна');
            $form->markAsError();
        }

        if (!$data['statusId'] && $data['statusDate']) {
            $form->statusId->addError('Статус на марката е задължителен');
            $form->markAsError();
        }
    }

    /**
     * {@inheritDoc}
     * @see \Micro\Application\Controller\Crud::modifyData()
     */
    protected function modifyData(array &$data)
    {
        if (empty($data['statusId']) || empty($data['statusDate'])) {
            unset($data['statusId']);
            unset($data['statusDate']);
            unset($data['statusNote']);
            unset($data['price']);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Light\Controller\Crud::prepareForm()
     */
    protected function prepareForm(Form $form, EntityInterface $item)
    {
        $classes = $item->getClasses();

        if ($classes) {
            $form->classes->setValue(explode(',', $classes));
        }

        $form->statusId->setValue("");
        $form->statusDate->setValue("");
        $form->statusNote->setValue("");
        $form->price->setValue("");

        if ($item->getId() && $item->getCountryId()) {
            $countriesModel = new Countries();
            $country = $countriesModel->find((int) $item->getCountryId());
            if ($country) {
                $form->price->setValue($country['price']);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Light\Controller\Crud::modifyEntity()
     */
    protected function modifyEntity(EntityInterface $entity)
    {
        $entity->setClasses(implode(',', $entity->getClasses()));
    }

    public function wizzardAction()
    {
        $form = new Form(package_path('Brands', 'Resources/forms/admin/wizzard.php'));

        if ($this->request->isPost()) {

            $post = $this->request->getPost();

            $post = Utils::arrayMapRecursive('trim', $post);

            if (isset($post['btnBack'])) {
                return new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));
            }

            $form->isValid($post);

            if (isset($post['name']) && $post['name'] && isset($post['countryId']) && $post['countryId']) {
                $m = new \Brands\Model\Table\Brands();
                foreach ($post['countryId'] as $countryId) {
                    $where = array('name = ?' => $post['name'], 'countryId = ?' => $countryId, 'typeId = ?' => $post['typeId']);
                    if ($m->fetchRow($where)) {
                        $form->countryId->addError('Тази марка и тип съществува за някои от избраните държави');
                        $form->markAsError();
                        break;
                    }
                }
            }

            if (!$form->hasErrors()) {

                $redirectResponse = new RedirectResponse(route(\null, ['action' => 'index', 'id' => \null]));

                try {
                    $post = Utils::arrayMapRecursive('trim', $post, true);
                    $this->getModel()->multipleInsert($post);
                    return $redirectResponse->withFlash('Информацията е записана');
                } catch (\Exception $e) {
                    return $redirectResponse->withFlash($e->getMessage(), 'danger');
                }
            }
        }

        $this->view->assign('form', $form);
    }

    /**
     * Delete selected status from history
     * @return RedirectResponse
     */
    public function deleteStatusAction()
    {
        $id = (int) $this->request->getParam('id');
        $brandId = (int) $this->request->getParam('brandId');

        $rel = new \Brands\Model\Table\BrandsStatusesRel();
        $row = $rel->find($id)->current();

        if ($row !== null) {
            $row->delete();
            $this->getModel()->fixLastStatus($brandId);
        }

        $redirect = new RedirectResponse(route(null, array('action' => 'edit', 'id' => $brandId, 'brandId' => null)));
        $redirect->withFlash(sprintf('Запис %s беше изтрит', $id));

        return $redirect;
    }
}