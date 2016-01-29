<?php

return [
    'home' => [
        'pattern' => '/',
        'handler' => 'App\Controller\Front\Index@index'
    ],
    'error' => [
        'pattern' => '/error',
        'handler' => 'App\Controller\Front\Error@index'
    ],
    'login' => [
        'pattern' => '/login',
        'handler' => 'UserManagement\Controller\Front\Index@login'
    ],
    'logout' => [
        'pattern' => '/logout',
        'handler' => 'UserManagement\Controller\Front\Index@logout'
    ],
    'profile' => [
        'pattern' => '/profile',
        'handler' => 'UserManagement\Controller\Front\Index@profile'
    ]
];