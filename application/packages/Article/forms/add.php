<?php

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
        'active' => [
            'type' => 'checkbox'
        ],
        'btnSave' => [
            'type' => 'submit', 'options' => ['value' => 'Запази']
        ],
        'protect' => 'csrf'
    ]
];