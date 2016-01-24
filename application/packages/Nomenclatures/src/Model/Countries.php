<?php

namespace Nomenclatures\Model;

use Micro\Database\Model\ModelAbstract;

class Countries extends ModelAbstract
{
    protected $_name = 'nom_countries';

    protected $_dependentTables = array(CountriesLang::class);
}