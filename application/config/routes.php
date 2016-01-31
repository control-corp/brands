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
    ]
];