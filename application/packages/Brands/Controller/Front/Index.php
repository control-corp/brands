<?php

namespace Brands\Controller\Front;

use Micro\Application\Controller\Crud;
use Micro\Http\Response;
use Micro\Model\EntityInterface;
use Micro\Form\Form;
use Micro\Database\Expr;

class Index extends Crud
{
    protected $ipp = 30;

    protected $model = \Brands\Model\Brands::class;

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
        $response = parent::indexAction();

        if ($response instanceof Response) {
            return $response;
        }

        $form = new Form(package_path('Brands', 'Resources/forms/index-filters.php'));

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
                    AND TIMESTAMPDIFF(MONTH, "' . $now . '", reNewDate) + 1 = ' . $months . '
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
            $where = array('name = ?' => $data['name'], 'countryId = ?' => $data['countryId']);
            if ($item->getId()) {
                $where['id <> ?'] = $item->getId();
            }
            if ($m->fetchRow($where)) {
                $form->countryId->addError('Марката съществува за тази държава');
                $form->markAsError();
            }
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
    }

    /**
     * (non-PHPdoc)
     * @see \Light\Controller\Crud::modifyEntity()
     */
    protected function modifyEntity(EntityInterface $entity)
    {
        $entity->setClasses(implode(',', $entity->getClasses()));
    }
}