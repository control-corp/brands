<?php

return [
    'elements' => [
        'username' => [
            'type' => 'text',
            'options' => [
                'required' => 1,
                'attributes' => [
                    'placeholder' => 'username'
                ]
            ]
        ],
        'password' => [
            'type' => 'password',
            'options' => [
                'required' => 1,
                'attributes' => [
                    'placeholder' => 'password'
                ]
            ]
        ],
        'enum' => [
            'type' => 'select',
            'options' => [
                'required' => 1,
                'emptyOption' => '---',
                'multiOptions' => [
                    '1' => 'Da',
                    '2' => 'Ne'
                ]
            ]
        ],
        'protect' => 'csrf'
    ]
];