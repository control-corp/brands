<?php

return [
    'application' => [
        'debug' => 1,
        'perfomance' => 0
    ],
    'packages' => [
        'App' => 'application/packages/App',
        'Nomenclatures' => 'application/packages/Nomenclatures',
        'UserManagement' => 'application/packages/UserManagement'
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
        'name' => 'TEST'
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