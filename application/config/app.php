<?php

return [
    'acl' => 1,
    'log' => [
        'enabled' => 1,
        'path' => 'data/log',
    ],
    'debug' => [
        'handlers' => [
            'dev_tools' => 1,
            'fire_php' => 1,
            //'performance' => 'data/classes.php'
        ],
    ],
    'error' => [
        'route' => 'error'
    ],
    'view' => [
        'paths' => [
            'application/resources',
        ]
    ],
    'session' => [
        'name' => 'TEST',
        'save_path' => 'data/session'
    ],
    'translator' => [
        'adapter' => 'TranslatorArray',
        'options' => [
            'path' => 'data/languages'
        ]
    ],
    'config' => [
        'js' => [
            'datepicker' => [
                'format' => 'dd.mm.yyyy',
            ],
        ],
    ]
];