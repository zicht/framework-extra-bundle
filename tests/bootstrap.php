<?php

if (!is_dir(__DIR__ . '/../vendor/')) {
    die('Vendors not installed. Have you tried "composer.phar install"?' . "\n");
}

$loader = require __DIR__ . '/../vendor/autoload.php';

return $loader;
