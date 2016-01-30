<?php

return [
    'acl' => 1,
    'debug' => [
        'handlers' => [
            'dev_tools' => 1,
            'fire_php' => 1,
            'performance' => 0
        ],
    ],
    'packages' => [
        'App' => 'application/packages/App',
        'Brands' => 'application/packages/Brands',
        'Nomenclatures' => 'application/packages/Nomenclatures',
        'UserManagement' => 'application/packages/UserManagement',
        'Navigation' => 'application/packages/Navigation',
        'MicroDebug' => 'library/Micro/packages/MicroDebug'
    ],
    'routes' => include 'application/config/routes.php',
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
    'db' => [
        'default' => 'localhost',
        'adapters' => [
            'localhost' => [
                'adapter'  => 'mysqli',
                'host'     => 'localhost',
                'dbname'   => 'brands_micro',
                'username' => 'root',
                'password' => '',
                'charset'  => 'utf8'
            ]
        ]
    ],
    'cache' => [
        'default'  => 'file',
        'adapters' => [
            'file' => [
                'frontend' => [
                    'adapter' => 'Core',
                    'options' => [
                        'lifetime' => (3600 * 24),
                        'automatic_serialization' => \true
                    ]
                ],
                'backend' => [
                    'adapter' => 'File',
                    'options' => [
                        'cache_dir' => 'data/cache'
                    ]
                ]
            ]
        ]
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