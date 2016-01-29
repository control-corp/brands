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
        'btnSave'  => ['type' => 'submit', 'options' => ['value' => 'Запазване']],
        'btnApply' => ['type' => 'submit', 'options' => ['value' => 'Прилагане']],
        'btnBack'  => ['type' => 'submit', 'options' => ['value' => 'Назад']],
        'protect'  => 'csrf'
    ]
];