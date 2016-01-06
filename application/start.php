<?php

use Micro\Application\Application;
use Micro\Application\Router;

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

if ((file_exists($file = 'application/config/dependencies.php')) === \true) {
    include $file;
}

return $app;