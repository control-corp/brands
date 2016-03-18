<?php

namespace Domains\Controller\Admin;

use Micro\Application\Controller\Crud;
use Micro\Http\Response;
use Micro\Form\Form;
use Micro\Model\EntityInterface;

class Index extends Crud
{
    protected $ipp = 30;

    protected $model = \Domains\Model\Domains::class;

    protected $scope = 'admin';

    /**
     * (non-PHPdoc)
     * @see \Micro\Application\Controller::init()
     */
    public function init()
    {
        parent::init();

        $nomNotifiers = new \Nomenclatures\Model\Notifiers();
        $this->view->assign('nomNotifiers', $nomNotifiers->fetchCachedPairs());
    }

    public function indexAction()
    {
        if (($response = parent::indexAction()) instanceof Response) {
            return $response;
        }

        $form = new Form(package_path('Domains', 'Resources/forms/admin/index-filters.php'));

        $form->populate($this->view->filters);

        $this->view->assign('form', $form);
    }

    /**
     * {@inheritDoc}
     * @see \Micro\Application\Controller\Crud::postValidate()
     */
    protected function postValidate(Form $form, EntityInterface $item, array $data)
    {
        if ($data['dateStart'] && $data['dateEnd']) {
            $dateStart = new \DateTime($data['dateStart']);
            $dateEnd = new \DateTime($data['dateEnd']);
            $diffInterval = $dateStart->diff($dateEnd);
            if ($diffInterval->invert) {
                $form->dateEnd->addError('Крайната дата трябва да бъде след началната');
            }
        }
    }
}