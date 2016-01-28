<?php

namespace Nomenclatures\Controller\Front;

use Micro\Application\Controller\Crud;
use Micro\Http\Response;
use Micro\Form\Form;

class Cities extends Crud
{
    public function indexAction()
    {
        if (($response = parent::indexAction()) instanceof Response) {
            return $response;
        }

        $form = new Form(package_path('Nomenclatures', 'Resources/forms/cities-filters.php'));

        $form->populate($response->filters);

        return $response->addData(['form' => $form]);
    }
}