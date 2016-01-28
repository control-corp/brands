<?php

return [
    'guest' => [
        'resources' => [
            'App\\Controller\\Front\\Index@index' => true,
            'UserManagement\\Controller\\Front\\Index@register' => true,
            'UserManagement\\Controller\\Front\\Index@login' => true,
        ],
        'parent' => \null
    ],
    'user' => [
        'resources' => [
            'App\\Controller\\Front\\Index@index' => true,
            'App\\Controller\\Front\\Index\\Ajax@something' => true,
            'App\\Controller\\Admin\\Index@index' => true,

            'Nomenclatures\\Controller\\Front\\Cities@index' => true,
            'Nomenclatures\\Controller\\Front\\Cities@add' => true,
            'Nomenclatures\\Controller\\Front\\Cities@edit' => true,
            'Nomenclatures\\Controller\\Front\\Cities@delete' => true,
            'Nomenclatures\\Controller\\Front\\Cities@view' => true,

            'Nomenclatures\\Controller\\Front\\Countries@index' => true,
            'Nomenclatures\\Controller\\Front\\Countries@add' => true,
            'Nomenclatures\\Controller\\Front\\Countries@edit' => true,
            'Nomenclatures\\Controller\\Front\\Countries@delete' => true,
            'Nomenclatures\\Controller\\Front\\Countries@view' => true,

            'UserManagement\\Controller\\Front\\Index@profile' => true,
            'UserManagement\\Controller\\Front\\Index@logout' => true,
            'UserManagement\\Controller\\Front\\Rights@index' => true
        ],
        'parent' => \null
    ],
    'admin' => [
        'resources' => [],
        'parent' => 'user'
    ]
];
