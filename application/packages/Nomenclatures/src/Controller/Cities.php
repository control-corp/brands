<?php

namespace Nomenclatures\Controller;

use Micro\Application\Controller\Crud;

class Cities extends Crud
{
    protected $model = \Nomenclatures\Model\Cities::class;
}