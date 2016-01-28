<?php

namespace Nomenclatures\Model\Entity;

use Micro\Model\EntityAbstract;

class City extends EntityAbstract
{
    protected $id;
    protected $country_id;
    protected $language_id;
    protected $name;
    protected $active = 1;
}