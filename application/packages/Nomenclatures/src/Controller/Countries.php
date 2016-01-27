<?php

namespace Nomenclatures\Controller;

use Micro\Application\Controller\Crud;
use Micro\Http\Response;
use Micro\Form\Form;

class Countries extends Crud
{
    public function index()
    {
        if (($response = parent::index()) instanceof Response) {
            return $response;
        }

        $form = new Form(package_path(current_package(), 'forms/countries-filters.php'));

        $form->populate($response->filters);

        return $response->addData(['form' => $form]);
    }
}