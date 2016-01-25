<?php

use Micro\Application\Application;
use Micro\Application\Router;
use Micro\Session\Session;
use Micro\Database\Database;

require 'library/Micro/autoload.php';

require __DIR__ . '/helpers.php';

/**
 * Cached mapped classes / dirs
 */
if ((file_exists($classes = 'application/data/classes.php')) === \true) {
    \MicroLoader::setFiles(include $classes);
}

$app = new Application(include 'application/config/app.php');

$app['language'] = 2;

/**
 * Create router with routes
 */
$app['router'] = function () use ($app) {
    $router = new Router($app['request']);
    $router->mapFromConfig(include 'application/config/routes.php');
    return $router;
};

/**
 * Create default db adapter
 */
$app['db'] = function () use ($app) {
    $dbConfig = $app['config']->get('db', []);
    $adapter  = $dbConfig['adapters'][$dbConfig['default']];
    return Database::factory($adapter['adapter'], $adapter);
};

/**
 * Register session config
 */
Session::register($app['config']->get('session', []));

return $app;