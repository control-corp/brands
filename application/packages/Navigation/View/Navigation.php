<?php
namespace Navigation\View;

use Micro\Navigation\Navigation as NavigationContainer;

class Navigation
{

    protected static $menus = [];

    public function __construct()
    {
        static::$menus['main'] = new NavigationContainer([
            [
                'label' => 'Начало',
                'alias' => 'home',
                'route' => 'home'
            ],
            [
                'label' => 'Вход',
                'alias' => 'login',
                'route' => 'login'
            ],
            [
                'label' => 'Профил',
                'alias' => 'profile',
                'route' => 'profile'
            ],
            [
                'label' => 'Групи',
                'alias' => 'groups',
                'route' => 'default',
                'routeParams' => [
                    'package' => 'user-management',
                    'controller' => 'groups'
                ]
            ],
            [
                'label' => 'Номенклатури',
                'alias' => 'nomenclatures',
                'route' => 'default',
                'routeParams' => [
                    'package' => 'nomenclatures',
                    'controller' => 'brand-classes'
                ],
                'pages' => [
                    [
                        'label' => 'Класове марки',
                        'alias' => 'nomenclatures.brand.classes',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'brand-classes'
                        ]
                    ],
                    [
                        'label' => 'Заявители',
                        'alias' => 'nomenclatures.notifiers',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'notifiers'
                        ]
                    ],
                    [
                        'label' => 'Статуси',
                        'alias' => 'nomenclatures.statuses',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'statuses'
                        ]
                    ],
                    [
                        'label' => 'Типове марки',
                        'alias' => 'nomenclatures.types',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'types'
                        ]
                    ],
                    [
                        'label' => 'Континенти',
                        'alias' => 'nomenclatures.continents',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'continents'
                        ]
                    ],
                    [
                        'label' => 'Държави',
                        'alias' => 'nomenclatures.countries',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'nomenclatures',
                            'controller' => 'countries'
                        ]
                    ]
                ]
            ],
            [
                'label' => 'Марки',
                'alias' => 'brands',
                'route' => 'default',
                'routeParams' => [
                    'package' => 'brands',
                ],
            ],
            [
                'label' => 'Изтекли марки',
                'alias' => 'brands.expired',
                'route' => 'default',
                'routeParams' => [
                    'package' => 'brands',
                    'controller' => 'expired',
                ],
            ],
            [
                'label' => 'Справки',
                'alias' => 'reports',
                'route' => 'default',
                'routeParams' => [
                    'package' => 'brands',
                    'controller' => 'reports',
                    'action' => 'brands',
                ],
                'pages' => [
                    [
                        'label' => 'Марка',
                        'alias' => 'reports.brands',
                        'route' => 'default',
                        'routeParams' => [
                            'package' => 'brands',
                            'controller' => 'reports',
                            'action' => 'brands',
                        ],
                    ]
                ]
            ],
            [
                'label' => 'Изход',
                'alias' => 'logout',
                'route' => 'logout'
            ]
        ]);
    }

    public function __invoke($menuId = 'main')
    {
        return static::$menus[$menuId];
    }
}