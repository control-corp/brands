<?php

namespace Navigation\View;

use Micro\Navigation\Navigation as NavigationContainer;
use Micro\Navigation\Page\Page as NavigationPage;
use Navigation\Helper;

class Navigation
{
    protected static $menus = [];

    public function __construct()
    {
        //static::$menus['main'] = new NavigationContainer(include package_path('Navigation', 'Resources/menu-main.php'));
        //static::$menus['left'] = new NavigationContainer(include package_path('Navigation', 'Resources/menu-left.php'));
    }

    public function __invoke($menuId)
    {
        if (!isset(static::$menus[$menuId])) {

            $container = new NavigationContainer();

            $tree = new Helper\Tree($menuId);

            $container->setPages($this->buildPages($tree->getTree()));

            static::$menus[$menuId] = $container;
        }

        return static::$menus[$menuId];
    }

    public static function getMenus()
    {
        return static::$menus;
    }

    public function buildPages(array $tree, $parent = \null)
    {
        $pages = array();

        foreach ($tree as $item) {

            $page = new NavigationPage([
                'id'      => $item['id'],
                'label'   => $item['name'],
                'alias'   => ($item['alias'] ? $item['alias'] : $item['id']),
                'visible' => 1,
                'route'   => $item['route'],
                'reset'   => $item['reset'],
                'qsa'     => $item['qsa'],
                'uri'     => $item['url'],
            ]);

            if ($item['url'] === \null) {

                $routeData = $item['routeData']
                             ? json_decode($item['routeData'], \true)
                             : [];

                $page->setRouteParams($routeData);
            }

            if (!empty($item['children'])) {
                $page->setPages(
                    $this->buildPages($item['children'], $page)
                );
            }

            $pages[] = $page;
        }

        return $pages;
    }
}