<?php

return [
    'env' => 'development',
    'debug' => 1,
    'application' => [
        'packages' => [
            'App'
        ],
        'packages_paths' => [
            __DIR__ . '/packages'
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
    ]
];