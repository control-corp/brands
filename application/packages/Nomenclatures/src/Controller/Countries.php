<?php

namespace Nomenclatures\Controller;

use Micro\Application\Controller\Crud;

class Countries extends Crud
{
    protected $model = \Nomenclatures\Model\Countries::class;
}