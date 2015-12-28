<?php

if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));

try {
    $app = include 'application/boot.php';
    $app->run();
} catch (\Exception $e) {
    if (env('development')) {
        echo $e->getMessage();
    }
}