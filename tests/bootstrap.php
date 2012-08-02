<?php
$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Run "composer update --dev" so that you\'ll be able to run the test suite');
}
$loader = require $file;
$loader->add('Test\\Midgard', __DIR__);

require __DIR__ . '/__files/MockMapper.php';
require __DIR__ . '/__files/MockWorkflow.php';