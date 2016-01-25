<?php

namespace Nomenclatures\Model;

class Countries extends \Micro\Model\ModelAbstract
{
    protected $table = Table\Countries::class;

    protected $entity = Entity\Country::class;
}