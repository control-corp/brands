<?php

use Nomenclatures\Model\Countries;

return [
    'elements' => [
        'name' => [
            'type' => 'text',
            'options' => [
                'required' => 1,
                'attributes' => [
                    'placeholder' => 'name'
                ]
            ]
        ],
        'country_id' => [
            'type' => 'select',
            'options' => [
                'required' => 1,
                'emptyOption' => 'Избери',
                'multiOptions' => (new Countries)->fetchPairs(),
                'attributes' => [
                    'placeholder' => 'name'
                ]
            ]
        ],
        'btnSave' => [
            'type' => 'submit', 'options' => ['value' => 'Запази']
        ],
        'protect' => 'csrf'
    ]
];