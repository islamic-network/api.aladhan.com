<?php
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Model\Locations;
use AlAdhanApi\Handler\AlAdhanHandler;
use AlAdhanApi\Handler\AlAdhanNotFoundHandler;
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$container['helper'] = function($c) {
    $helper = new \stdClass();
    $helper->logger = new Log();

    return $helper;
};

$container['model'] = function($c) {
    $model = new \stdClass();
    $model->locations = new Locations($c['helper']->logger);

    return $model;
};

$container['notFoundHandler'] = function ($c) {
    return new AlAdhanNotFoundHandler();
};

$container['errorHandler'] = function ($c) {
    return new AlAdhanHandler();
};

