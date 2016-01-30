<?php

return [
    'elements' => [
        'username' => [
            'type'    => 'text',
            'options' => [
                'required' => 1,
                'class' => 'form-control',
                'attributes' => ['placeholder' => 'username']
            ]
        ],
        'password' => [
            'type'    => 'password',
            'options' => [
                'required' => 1,
                'class' => 'form-control',
                'attributes' => ['placeholder' => 'password']
            ]
        ]
    ]
];