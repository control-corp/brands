<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class Cities extends TableAbstract
{
    protected $_name = 'nom_cities';

    protected $_dependentTables = array(CitiesLang::class);
}