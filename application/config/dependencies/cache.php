<?php

use Micro\Cache\Cache;

$app['caches'] = function () use ($app) {
    $adapters = $app['config']->get('cache.adapters', []);
    $caches = [];
    foreach ($adapters as $adapter => $config) {
        $caches[$adapter] = Cache::factory(
                $config['frontend']['adapter'], $config['backend']['adapter'],
                $config['frontend']['options'], $config['backend']['options']
        );
    }
    return $caches;
};

$app['cache'] = function () use ($app) {
    return $app->get('caches')[$app['config']->get('cache.default')];
};