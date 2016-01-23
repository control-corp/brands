<?php

chdir(dirname(__DIR__));

putenv('APP_ENV=development');

ob_start();

$app = include 'application/start.php';

$app->run();