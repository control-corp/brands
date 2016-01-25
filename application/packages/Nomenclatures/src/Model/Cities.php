<?php

namespace Nomenclatures\Model;

class Cities extends \Micro\Model\ModelAbstract
{
    protected $table = Table\Cities::class;

    protected $entity = Entity\City::class;
}