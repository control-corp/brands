<?php

use Micro\Application\Application;
use Micro\Application\Utils;

include_once 'library/Micro/autoload.php';

if (is_file($composer = 'vendor/autoload.php')) {
    include_once $composer;
}

MicroLoader::register();

if ((is_file($classes = 'data/classes.php')) === \true) {
    MicroLoader::setFiles(include $classes);
}

$config = [];

foreach (glob('{application/config/*.php,application/config/packages/*.php}', GLOB_BRACE) as $file) {
    $config = Utils::merge($config, include $file);
}

if (isset($config['packages'])) {
    MicroLoader::addPath($config['packages']);
}

$app = new Application($config);

/* $app->set('exception.handler', function ($container) {

    $whoops = new Whoops\Run;

    if ($container->get('request')->isAjax()) {
        $whoops->pushHandler(new Whoops\Handler\JsonResponseHandler);
    } else {
        $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
    }

    $whoops->register();

    return new App\Bridge\Whoops($whoops);
}); */

$app->registerDefaultServices();

$app->get('router')->mapFromConfig()->loadDefaultRoutes();

return $app;