<?php

use MacFJA\PharBuilder\Application;

$autoloaders = array(
    // Path in application context (PharBuilder as project base)
    __DIR__ . '/../vendor/autoload.php',
    // Path in vendor context (PharBuilder as dependency)
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
