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
    ],
    'default' => [
        'pattern' => '/{package}[/{controller}][/{action}]',
        'handler' => function ($route) {
            $params = array_map('ucfirst', array_map('Micro\Utils::camelize', $route->getParams()));
            return $params['package'] . '\\Controller\\' . $params['controller'] . '@' . lcfirst($params['action']);
        },
        'defaults' => ['package' => 'app', 'controller' => 'index', 'action' => 'index']
    ]
];