<?php

namespace Nomenclatures\Model;

use Micro\Database\Model\ModelAbstract;

class CitiesLang extends ModelAbstract
{
    protected $_name = 'nom_cities_lang';

    protected $_referenceMap = array(
        'City' => array(
            'columns' => 'city_id',
            'refTableClass' => Cities::class,
            'refColumns' => 'id',
        ),
    );
}