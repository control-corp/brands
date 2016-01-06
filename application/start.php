<?php

use Micro\Application\Application;
use Micro\Application\Router;
use Micro\Session\Session;

require 'library/Micro/autoload.php';

if ((file_exists($classes = 'application/data/classes.php')) === \true) {
    \MicroLoader::setFiles(include $classes);
}

$app = new Application(include 'application/config/app.php');

$app['router'] = function () use ($app) {
    $router = new Router($app['request']);
    $router->mapFromConfig(include 'application/config/routes.php');
    return $router;
};

foreach (\glob('application/config/dependencies/*.php') as $file) {
    include $file;
}

Session::register($app['config']->get('session', []));

return $app;