<?php

require __DIR__ . '/vendor/autoload.php';

$redis = new \Predis\Client([
    'scheme' => 'tcp',
    'host' => 'host.docker.internal',
    'port' => 6379,
    'password' => '',
    'database' => 0,
]);
