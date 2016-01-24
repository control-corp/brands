<?php


return [
    'home' => [
        'pattern' => '/',
        'handler' => 'App\Controller\Index@index'
    ],
    'error' => [
        'pattern' => '/error',
        'handler' => 'App\Controller\Error@index'
    ],
    'login' => [
        'pattern' => '/login',
        'handler' => 'UserManagement\Controller\Index@login'
    ],
    'logout' => [
        'pattern' => '/logout',
        'handler' => 'UserManagement\Controller\Index@logout'
    ],
    'register' => [
        'pattern' => '/register',
        'handler' => 'UserManagement\Controller\Index@register'
    ],
    'profile' => [
        'pattern' => '/profile',
        'handler' => 'UserManagement\Controller\Index@profile'
    ],
    'rights' => [
        'pattern' => '/rights',
        'handler' => 'UserManagement\Controller\Rights@index'
    ]
];