<?php

require 'library/Micro/autoload.php';

if ((file_exists($classes = 'application/data/classes.php')) === \true) {
    MicroLoader::setFiles(include $classes);
}

$app = new Micro\Application(
    include 'application/config.php'
);

$app['request'] = function () {
    return new Micro\Http\Request();
};

$app['response'] = function () {
    return new Micro\Http\Response\HtmlResponse();
};

$app['event'] = function () {
    return new Micro\Event\Manager();
};

$app['router'] = function ($c) {

    $router = new Micro\Application\Router($c['request']);

    if ((file_exists($file = 'application/boot/routes.php')) === \true) {
        foreach (include $file as $name => $config) {
            $route = $router->map($name, $config['pattern'], $config['handler']);
            if (isset($config['conditions'])) {
                $route->setConditions($config['conditions']);
            }
            if (isset($config['defaults'])) {
                $route->setDefaults($config['defaults']);
            }
        }
    }

    return $router;
};

if ((file_exists($file = 'application/boot/services.php')) === \true) {
    include $file;
}

return $app;