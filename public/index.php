<?php


chdir(dirname(__DIR__));

try {
    $app = include 'application/start.php';
    $app->run();
} catch (\Exception $e) {
    if (env('development')) {
        echo $e->getMessage();
    }
}