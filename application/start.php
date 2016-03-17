<?php

use Micro\Application\Application;
use Micro\Application\Utils;

$config = [];

foreach (glob('{application/config/*.php,application/config/packages/*.php}', GLOB_BRACE) as $file) {
    $config = Utils::merge($config, include $file);
}

if (isset($config['packages'])) {
    MicroLoader::addPath($config['packages']);
}

$app = new Application($config);

$app->registerDefaultServices();

$app->get('router')->mapFromConfig()->loadDefaultRoutes();

return $app;