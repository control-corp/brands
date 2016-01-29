<?php

namespace Navigation\View;

use Micro\Navigation\Navigation as NavigationContainer;

class Navigation
{
    protected static $menus = [];

    public function __construct()
    {
        static::$menus['main'] = new NavigationContainer([
            ['label' => 'Начало', 'alias' => 'home', 'route' => 'home'],
            ['label' => 'Вход', 'alias' => 'login', 'route' => 'login'],
            ['label' => 'Профил', 'alias' => 'profile', 'route' => 'profile'],
            ['label' => 'Групи', 'alias' => 'groups', 'route' => 'default', 'routeParams' => ['package' => 'user-management', 'controller' => 'groups']],
            ['label' => 'Градове', 'alias' => 'nomenclatures.cities', 'route' => 'default', 'routeParams' => ['package' => 'nomenclatures', 'controller' => 'cities']],
            ['label' => 'Държави', 'alias' => 'nomenclatures.countries', 'route' => 'default', 'routeParams' => ['package' => 'nomenclatures', 'controller' => 'countries']],
            ['label' => 'Изход', 'alias' => 'logout', 'route' => 'logout'],
        ]);
    }

    public function __invoke($menuId = 'main')
    {
        return static::$menus[$menuId];
    }
}