<?php

return [
    'debug' => [
        'handlers' => [
            'dev_tools' => 1,
            'fire_php' => 1,
            'performance' => 0
        ],
    ],
    'packages' => [
        'App' => 'application/packages/App',
        'Nomenclatures' => 'application/packages/Nomenclatures',
        'UserManagement' => 'application/packages/UserManagement',
        //'MicroDebug' => 'library/Micro/packages/MicroDebug'
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
        'save_path' => 'application/data/session'
    ],
    'db' => [
        'default' => 'localhost',
        'adapters' => [
            'localhost' => [
                'adapter'  => 'mysqli',
                'host'     => 'localhost',
                'dbname'   => 'micro',
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
                        'cache_dir' => 'application/data/cache'
                    ]
                ]
            ]
        ]
    ]
];