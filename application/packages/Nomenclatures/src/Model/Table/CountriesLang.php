<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class CountriesLang extends TableAbstract
{
    protected $_name = 'nom_countries_lang';

    protected $_referenceMap = array(
        array(
            'columns' => 'country_id',
            'refTableClass' => Countries::class,
            'refColumns' => 'id',
        ),
    );
}