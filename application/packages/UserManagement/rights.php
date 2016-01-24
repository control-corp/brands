<?php
return array(
    'guest' => array(
        'resources' => array(
            'App\\Controller\\Error@index' => true,
            'App\\Controller\\Index@index' => true,
            'Nomenclatures\\Controller\\Cities@index' => true,
            'Nomenclatures\\Controller\\Cities@add' => true,
            'Nomenclatures\\Controller\\Cities@edit' => true,
            'Nomenclatures\\Controller\\Cities@delete' => true,
            'Nomenclatures\\Controller\\Cities@view' => true,
            'Nomenclatures\\Controller\\Countries@index' => true,
            'Nomenclatures\\Controller\\Countries@add' => true,
            'Nomenclatures\\Controller\\Countries@edit' => true,
            'Nomenclatures\\Controller\\Countries@delete' => true,
            'Nomenclatures\\Controller\\Countries@view' => true,
            'UserManagement\\Controller\\Index@profile' => true,
            'UserManagement\\Controller\\Index@register' => true,
            'UserManagement\\Controller\\Index@login' => true,
            'UserManagement\\Controller\\Index@logout' => true,
            'UserManagement\\Controller\\Rights@index' => true
        ),
        'parent' => NULL
    ),
    'user' => array(
        'resources' => array(),
        'parent' => 'guest'
    ),
    'admin' => array(
        'resources' => array(),
        'parent' => 'user'
    )
);
