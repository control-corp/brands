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
    'article.list' => [
        'pattern' => '/article',
        'handler' => 'Article\Index@index'
    ],
    'article.detail' => [
        'pattern' => '/article/{id}',
        'handler' => 'Article\Index@detail'
    ]
];