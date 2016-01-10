<?php

return [
    'application' => [
        'debug' => 1,
        'perfomance' => 0
    ],
    'packages' => [
        'registered' => [
            'App'
        ],
        'paths' => [
            'application/packages'
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