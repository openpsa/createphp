<?php
$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Run "composer update --dev" so that you\'ll be able to run the test suite');
}
require $file;

require __DIR__ . '/__files/MockMapper.php';
require __DIR__ . '/__files/MockWorkflow.php';