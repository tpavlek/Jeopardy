<?php

require 'vendor/autoload.php';

$question_filename = isset($argv[1]) ? $argv[1] : "questions";

$server = new \Depotwarehouse\Jeopardy\Server(\React\EventLoop\Factory::create());

try {
    $boardFactory = new \Depotwarehouse\Jeopardy\Board\BoardFactory($question_filename);
    $server->run($boardFactory);
} catch (\React\Socket\ConnectionException $exception) {
    echo "Error occurred: " . get_class($exception) . "\n";
    echo $exception->getMessage();
    die();
}

