<?php

return [
    /* 'acl' => [
        'enabled' => 1
    ], */
    /* 'auth' => [
        'namespace' => [
            'admin' => [
                'admin',
                'admin-login'
            ]
        ]
    ], */
    'log' => [
        'enabled' => 1,
        'path' => 'data/log',
    ],
    'error' => [
        'default' => 'App\Controller\Front\Error@index',
        'admin'   => 'App\Controller\Admin\Error@index',
        'admin-login' => 'App\Controller\Admin\Error@index',
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