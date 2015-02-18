<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once '../vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('views');

$twig = new Twig_Environment($loader);

$router = new \League\Route\RouteCollection();

$router->get('/', function (Request $request, Response $response) use ($twig) {
    $response->setContent($twig->render('index.html.twig'));
    return $response;
});

$router->addRoute('GET', '/admin', function (Request $request, Response $response) use ($twig) {
    $response->setContent($twig->render('admin.html.twig'));
    return $response;
});

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

$response->send();
