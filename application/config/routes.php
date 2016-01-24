<?php

use Micro\Application\Utils;
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
    ],
    'article.add' => [
        'pattern' => '/article/add',
        'handler' => 'Article\Controller\Index@add',
    ],
    'article.delete' => [
        'pattern' => '/article/{id}/delete',
        'handler' => 'Article\Controller\Index@delete',
        'conditions' => ['id' => '\d+']
    ],
    'default' => [
        'pattern' => '/{package}/{controller}/{action}[/{id}]',
        'handler' => function ($route) {
            $params = $route->getParams();
            return ucfirst(Utils::camelize($params['package'])) . '\\Controller\\' . ucfirst(Utils::camelize($params['controller'])) . '@' . lcfirst(Utils::camelize($params['action']));
        },
        'defaults' => ['package' => 'App', 'controller' => 'index', 'action' => 'index'],
        'conditions' => ['id' => '\d+']
    ]
];