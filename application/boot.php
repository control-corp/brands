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

    $router->map('home', '/', 'App\\Controller\\Index@index');
    $router->map('articles', '/articles', 'App\\Controller\\Index@articles');
    $router->map('article', '/article/{id}', 'App\\Controller\\Index@article');
    $router->map('error', '/error', 'App\\Controller\\Error@index');

    return $router;
};

return $app;