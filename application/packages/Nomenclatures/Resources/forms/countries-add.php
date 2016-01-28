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
        'btnSave' => [
            'type' => 'submit', 'options' => ['value' => 'Запази']
        ],
        'protect' => 'csrf'
    ]
];