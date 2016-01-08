<?php

return [
    'debug' => 1,
    'application' => [
        'packages' => [
            'App',
        ],
        'packages_paths' => [
            'application/packages'
        ],
        'perfomance' => [
            'enable' => 0
        ]
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