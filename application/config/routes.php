<?php

return [
    'home' => [
        'pattern' => '/',
        'handler' => 'App\Index@index'
    ],
    'error' => [
        'pattern' => '/error',
        'handler' => 'App\Error@index'
    ],
    'login' => [
        'pattern' => '/login',
        'handler' => 'App\Index@login'
    ],
    'logout' => [
        'pattern' => '/logout',
        'handler' => 'App\Index@logout'
    ],
    'register' => [
        'pattern' => '/register',
        'handler' => 'App\Index@register'
    ],
    'profile' => [
        'pattern' => '/profile',
        'handler' => 'App\Index@profile'
    ],
    'article.list' => [
        'pattern' => '/article',
        'handler' => 'Article\Index@index'
    ],
    'article.detail' => [
        'pattern' => '/article/{id}',
        'handler' => 'Article\Index@detail',
        'conditions' => ['id' => '\d+']
    ]
];