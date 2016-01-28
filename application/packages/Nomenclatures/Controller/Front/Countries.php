<?php

namespace Nomenclatures\Controller\Front;

use Micro\Application\Controller\Crud;
use Micro\Http\Response\RedirectResponse;
use Micro\Form\Form;

class Countries extends Crud
{
    public function indexAction()
    {
        if (($view = parent::indexAction()) instanceof RedirectResponse) {
            return $view;
        }

        $form = new Form(package_path('Nomenclatures', 'Resources/forms/countries-filters.php'));

        $form->populate($view->filters);

		if (($t = $this->request->getParam('template')) !== \null) {
			$view->setTemplate($t);
		}

        return $view->addData(['form' => $form]);
    }
}