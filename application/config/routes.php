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
    'register' => [
        'pattern' => '/register',
        'handler' => 'UserManagement\Controller\Front\Index@register'
    ],
    'profile' => [
        'pattern' => '/profile',
        'handler' => 'UserManagement\Controller\Front\Index@profile'
    ],
    'rights' => [
        'pattern' => '/rights',
        'handler' => 'UserManagement\Controller\Front\Rights@index'
    ]
];