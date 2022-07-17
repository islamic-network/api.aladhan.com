<?php
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Model\Locations;
use Psr7Middlewares\Middleware;

$container->set('helper', function($c) {
    $helper = new \stdClass();
    $helper->logger = new Log();

    return $helper;
});

$container->set('model', function($c) {
    $model = new \stdClass();
    $helper = $c->get('helper');
    $model->locations = new Locations($helper->logger);

    return $model;
});

// Application middleware
$errorMiddleware = $app->addErrorMiddleware($debug, true, true);

$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();
$errorHandler = new \AlAdhanApi\Handler\AlAdhanHandler($callableResolver, $responseFactory);
$errorMiddleware->setDefaultErrorHandler($errorHandler);


