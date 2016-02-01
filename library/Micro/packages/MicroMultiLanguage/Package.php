<?php

namespace MicroMultiLanguage;

use Micro\Application\Package as BasePackage;
use App\Model\Languages;
use App\Model\Entity\Language;

class Package extends BasePackage
{
    public function boot()
    {
        $this->container['event']->attach('application.start', array($this, 'onApplicationStart'));
        $this->container['event']->attach('route.end', array($this, 'onRouteEnd'));
    }

    public function onApplicationStart()
    {
        $this->container->set('languages', function ($container) {

            $container->get('db');

            $cache = $container->get('cache');

            if ($cache === \null || (($languages = $cache->load('Languages')) === \false)) {
                $m = new Languages();
                $languages = $m->getItems();
                if ($cache) {
                    $cache->save($languages, 'Languages');
                }
            }

            return $languages;
        });


        $languages = $this->container->get('languages');

        $this->container->set('language', function ($container) use ($languages) {

            $currentLanguage = \null;

            $defaultLanguage = $container['config']->get('language.default', 'bg');

            $lang = $container['request']->getParam('lang', $defaultLanguage);

            foreach ($languages as $language) {
                if ($language->getCode() === $lang) {
                    $currentLanguage = $language;
                    break;
                }
            }

            if ($currentLanguage === \null) {
                $currentLanguage = new Language(1, $defaultLanguage);
            }

            return $currentLanguage;
        });

        $router = $this->container->get('router');

        $validLanguages = [];

        foreach ($languages as $language) {
            $validLanguages[] = $language->getCode();
        }

        foreach ($router->getRoutes() as $route) {

            if ($route->getName() === 'home') {
                continue;
            }

            $pattern = $route->getPattern();

            if ($route->getName() === 'default') {
                $pattern = ltrim($pattern, '/');
            }

            $route->setPattern('/{lang}' . $pattern);

            if (!empty($validLanguages)) {
               $route->addCondition('lang', implode('|', $validLanguages));
            }
        }
    }

    public function onRouteEnd()
    {
        $router = $this->container->get('router');
        $routeParams = $router->getCurrentRoute()->getParams();

        if (isset($routeParams['lang'])) {
            $router->setGlobalParam('lang', $routeParams['lang']);
        }
    }
}