<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class Countries extends TableAbstract
{
    protected $_name = 'nom_countries';

    protected $_dependentTables = array(CountriesLang::class);
}