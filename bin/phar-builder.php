<?php

use MacFJA\PharBuilder\Application;

$autoloaders = array(
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
);

foreach ($autoloaders as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

$app = new Application();
$app->run();
