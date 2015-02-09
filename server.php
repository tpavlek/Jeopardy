<?php

require 'vendor/autoload.php';

$server = new \Depotwarehouse\Jeopardy\Server(\React\EventLoop\Factory::create());

$server->run();
