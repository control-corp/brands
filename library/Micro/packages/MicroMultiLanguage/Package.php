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
    }

    public function onApplicationStart()
    {
        $router = $this->container->get('router');

        foreach ($router->getRoutes() as $route) {

            if ($route->getName() === 'home') {
                continue;
            }

            $pattern = $route->getPattern();

            if ($route->getName() === 'default') {
                $pattern = ltrim($pattern, '/');
            }

            $route->setPattern('/{lang}' . $pattern);
        }

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

        $this->container->set('language', function ($container) {

            $currentLanguage = \null;

            $defaultLanguage = $container['config']->get('language.default', 'bg');
            $lang = $container['request']->getParam('lang', $defaultLanguage);

            foreach ($container['languages'] as $language) {
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

        $router->setGlobalParam('lang', $this->container->get('language')->getCode());
    }
}