<?php

namespace Nomenclatures\Model;

use Micro\Database\Model\ModelAbstract;

class CountriesLang extends ModelAbstract
{
    protected $_name = 'nom_countries_lang';

    protected $_referenceMap = array(
        'Country' => array(
            'columns' => 'country_id',
            'refTableClass' => Countries::class,
            'refColumns' => 'id',
        ),
    );
}