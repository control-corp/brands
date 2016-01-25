<?php

namespace Nomenclatures\Controller;

use Micro\Application\Controller\Crud;

class Countries extends Crud
{
    protected $model = \Nomenclatures\Model\Countries::class;

    /* public function index()
    {
        $entity = $this->getModel()->createEntity(['language_id' => 2, 'name' => 'test']);
        $this->getModel()->save($entity);
        die;
    } */
}