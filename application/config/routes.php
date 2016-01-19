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
        'handler' => 'App\Controller\Index@login'
    ],
    'logout' => [
        'pattern' => '/logout',
        'handler' => 'App\Controller\Index@logout'
    ],
    'register' => [
        'pattern' => '/register',
        'handler' => 'App\Controller\Index@register'
    ],
    'profile' => [
        'pattern' => '/profile',
        'handler' => 'App\Controller\Index@profile'
    ],
    'article.list' => [
        'pattern' => '/article',
        'handler' => 'Article\Controller\Index@index'
    ],
    'article.detail' => [
        'pattern' => '/article/{id}',
        'handler' => 'Article\Controller\Index@detail',
        'conditions' => ['id' => '\d+']
    ]
];