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
    ],
    'default' => [
        'pattern' => '/{package}[/{controller}][/{action}][/{id}]',
        'handler' => function ($route) {
            $params = $route->getParams();
            return ucfirst(Utils::camelize($params['package'])) . '\\Controller\\' . ucfirst(Utils::camelize($params['controller'])) . '@' . lcfirst(Utils::camelize($params['action']));
        },
        'defaults' => ['package' => 'app', 'controller' => 'index', 'action' => 'index'],
        'conditions' => ['id' => '\d+']
    ]
];