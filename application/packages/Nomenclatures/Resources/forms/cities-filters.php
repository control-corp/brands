<?php

use Nomenclatures\Model\Countries;

return [
    'elements' => [
        'name' => [
            'type' => 'text',
            'options' => [
                'label' => 'Име',
                'class' => 'form-control',
                'belongsTo' => 'filters',
            ]
        ],
        'country_id' => [
            'type' => 'select',
            'options' => [
                'label' => 'Държава',
                'isArray' => 1,
                'class' => 'form-control selectpicker',
                'belongsTo' => 'filters',
                'multiOptions' => (new Countries())->fetchPairs()
            ]
        ],
    ]
];