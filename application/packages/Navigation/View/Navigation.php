<?php

namespace Navigation\View;

use Micro\Navigation\Navigation as NavigationContainer;

class Navigation
{
    protected static $menus = [];

    public function __construct()
    {
        static::$menus['main'] = new NavigationContainer(include package_path('Navigation', 'Resources/menu-main.php'));
        static::$menus['left'] = new NavigationContainer(include package_path('Navigation', 'Resources/menu-left.php'));
    }

    public function __invoke($menuId = 'main')
    {
        return isset(static::$menus[$menuId]) ? static::$menus[$menuId] : \null;
    }
}