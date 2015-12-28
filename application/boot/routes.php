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
    'articles' => [
        'pattern' => '/articles',
        'handler' => 'App\Controller\Index@articles'
    ],
    'article' => [
        'pattern' => '/article/{id}',
        'handler' => 'App\Controller\Index@article'
    ]
];