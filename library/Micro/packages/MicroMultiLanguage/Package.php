<?php

namespace MicroMultiLanguage;

use Micro\Application\Package as BasePackage;
use App\Model\Languages;
use App\Model\Entity\Language;

class Package extends BasePackage
{
    const DEFAULT_LANG = 'bg';

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

            $defaultLanguage = $container['config']->get('language.default', static::DEFAULT_LANG);

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

        $validLanguages = [];

        foreach ($languages as $language) {
            $validLanguages[] = $language->getCode();
        }

        foreach ($this->container->get('router')->getRoutes() as $route) {

            $pattern = $route->getPattern();

            if ($route->getPattern() === '/') {
                $pattern = '';
            }

            $route->setPattern('[/{lang}]' . $pattern);

            if (!empty($validLanguages)) {
               $route->addCondition('lang', implode('|', $validLanguages));
            }
        }
    }

    public function onRouteEnd()
    {
        $router = $this->container->get('router');
        $request = $this->container->get('request');

        $router->setGlobalParam('lang', $request->getParam('lang', ''));
    }
}