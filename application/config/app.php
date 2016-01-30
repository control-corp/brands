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
    'db' => include __DIR__ . '/db.php',
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