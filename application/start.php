<?php

use Micro\Application\Application;

include 'library/Micro/autoload.php';

if (file_exists('vendor/autoload.php')) {
    include 'vendor/autoload.php';
}

/**
 * Cached mapped classes / dirs
 */
if ((file_exists($classes = 'application/data/classes.php')) === \true) {
    \MicroLoader::setFiles(include $classes);
}

$config = [];

foreach (glob('application/config/*.php') as $file) {
    $config = array_merge($config, include $file);
}

$app = new Application($config);

$app['language'] = function ($c) {
    return new \App\Model\Entity\Language(1, 'bg');
};

return $app;