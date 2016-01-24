<?php

namespace Nomenclatures\Model;

use Micro\Database\Model\ModelAbstract;

class Cities extends ModelAbstract
{
    protected $_name = 'nom_cities';

    protected $_dependentTables = array(CitiesLang::class);
}