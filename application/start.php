<?php

use Micro\Application\Application;

include_once 'library/Micro/autoload.php';

if (file_exists('vendor/autoload.php')) {
    include_once 'vendor/autoload.php';
}

/**
 * Cached mapped classes / dirs
 */
if ((file_exists($classes = 'data/classes.php')) === \true) {
    \MicroLoader::setFiles(include $classes);
}

$config = [];

foreach (glob('{application/config/*.php,application/config/packages/*.php}', GLOB_BRACE) as $file) {
    $config = array_merge($config, include $file);
}

$app = new Application($config);

$app->set('exception.handler', function ($container) {

    $whoops = new Whoops\Run;

    if ($container->get('request')->isAjax()) {
        $whoops->pushHandler(new Whoops\Handler\JsonResponseHandler);
    } else {
        $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
    }

    $whoops->register();

    return new App\Bridge\Whoops($whoops);
});

$app->registerDefaultServices();

$app->get('router')->mapFromConfig()->loadDefaultRoutes();

return $app;