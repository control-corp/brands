<?php

return [
    'routes' => [
        'home' => [
            'pattern' => '/',
            'handler' => 'App\Controller\Front\Index@index'
        ],
        'admin-login' => [
            'pattern' => '/admin/login',
            'handler' => 'UserManagement\Controller\Admin\Index@login'
        ],
        'pages.detail' => [
            'pattern' => '/page/{alias}-{id}.html',
            'handler' => 'Pages\Controller\Front\Index@detail',
            'conditions' => ['alias' => '[\w-]+', 'id' => '\d+'],
        ],
    ]
];