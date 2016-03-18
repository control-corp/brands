<?php

return [
    'routes' => [
        'home' => [
            'pattern' => '/',
            'handler' => 'UserManagement\Controller\Admin\Index@login'
        ],
        'admin-login' => [
            'pattern' => '/admin/login',
            'handler' => 'UserManagement\Controller\Admin\Index@login'
        ],
    ]
];