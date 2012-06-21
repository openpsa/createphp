<?php
$file = __dir__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Run "composer update --dev" so that you\'ll be able to run the test suite');
}
require $file;
