<?php

return [
    'db' => [
        'default' => 'localhost',
        'adapters' => [
            'localhost' => [
                'adapter'  => 'mysqli',
                'host'     => 'localhost',
                'dbname'   => 'brands',
                'username' => 'root',
                'password' => 'root',
                'charset'  => 'utf8'
            ]
        ]
    ]
];