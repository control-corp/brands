<?php

chdir(dirname(__DIR__));

putenv('APP_ENV=development');

$app = include 'application/start.php';

$app->run();