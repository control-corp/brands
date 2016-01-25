<?php

namespace Nomenclatures\Model\Table;

use Micro\Database\Table\TableAbstract;

class CitiesLang extends TableAbstract
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