<?php

require 'vendor/autoload.php';

$server = new \Depotwarehouse\Jeopardy\Server(\React\EventLoop\Factory::create());

try {
    $server->run();
} catch (\React\Socket\ConnectionException $exception) {
    echo "Error occurred: " . get_class($exception) . "\n";
    echo $exception->getMessage();
    die();
}

