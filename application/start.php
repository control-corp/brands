<?php

use Micro\Application\Application;

require 'library/Micro/autoload.php';

/**
 * Cached mapped classes / dirs
 */
if ((file_exists($classes = 'application/data/classes.php')) === \true) {
    \MicroLoader::setFiles(include $classes);
}

$app = new Application(include 'application/config/app.php');

$app['language'] = 2;

return $app;