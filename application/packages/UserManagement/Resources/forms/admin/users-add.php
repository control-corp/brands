<?php

use Micro\Database\Expr;

return [
    'elements' => [
        'username' => [
            'type'    => 'text',
            'options' => [
                'label' => 'Потребителско име',
                'required' => 1,
                'class' => 'form-control',
            ]
        ],
        'groupId' => [
            'type' => 'select',
            'options' => [
                'label' => 'Група',
                'required' => 1,
                'labelClass' => 'control-label',
                'class' => 'form-control',
                'emptyOption' => 'Избери',
                'multiOptions' => (new UserManagement\Model\Groups)->fetchCachedPairs(array(new Expr('alias <> "guest"'))),
            ],
        ],
        'password' => [
            'type'    => 'password',
            'options' => [
                'label' => 'Парола',
                'required' => 1,
                'class' => 'form-control',
                'attributes' => ['autocomplete' => 'off'],
                'validators' => array(
                    array(
                        'validator' => 'Identical',
                        'options'   => array(
                            'field' => 'repassword',
                            'error' => 'Паролите не съвпадат'
                        )
                    )
                )
            ]
        ],
        'repassword' => [
            'type'    => 'password',
            'options' => [
                'label' => 'Повтори паролата',
                'required' => 1,
                'class' => 'form-control',
                'attributes' => ['autocomplete' => 'off']
            ]
        ],
        'btnSave'  => ['type' => 'submit', 'options' => ['value' => 'Запазване', 'class' => 'btn btn-primary']],
        'btnApply' => ['type' => 'submit', 'options' => ['value' => 'Прилагане', 'class' => 'btn btn-success']],
        'btnBack'  => ['type' => 'submit', 'options' => ['value' => 'Назад', 'class' => 'btn btn-default']],
    ]
];