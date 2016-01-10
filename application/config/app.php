<?php

return [
    'application' => [
        'debug' => 0,
        'perfomance' => 0
    ],
    'packages' => [
        'App' => 'application/packages/App',
        'Article' => 'application/packages/Article',
    ],
    'error' => [
        'route' => 'error'
    ],
    'view' => [
        'paths' => [
            'application/resources',
            'application/resources/packages'
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
                        'automatic_serialization' => true
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