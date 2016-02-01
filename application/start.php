<?php

use Micro\Application\Application;

include_once 'library/Micro/autoload.php';

/* if (file_exists('vendor/autoload.php')) {
    include_once 'vendor/autoload.php';
} */

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

$app->registerDefaultServices();

$app['language'] = function ($c) {
    return new \App\Model\Entity\Language(1, 'bg');
};

$app['router']->mapFromConfig(
    $app['config']->get('routes', [])
);

return $app;