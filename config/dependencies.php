<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Database;
use AlAdhanApi\Model\Locations;


$container = $app->getContainer();

$container['helper'] = function($c) {
    $helper = new \stdClass();
    $helper->logger = new Log();
    // Write default Log
    // $helper->logger->write();
    // $helper->database = new Database();

    return $helper;
};

$container['model'] = function($c) {
    $model = new \stdClass();
    $model->locations = new Locations($c['helper']->logger);

    return $model;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $r = [
        'code' => 404,
        'status' => 'Not Found',
        'data' => 'Invalid endpoint or resource.'
        ];
        $resp = json_encode($r);

        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->write($resp);
    };
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $r = [
        'code' => 500,
        'status' => 'Internal Server Error',
        'data' => 'Something went wrong when the server tried to process this request. Sorry!'
        ];

        $resp = json_encode($r);

        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write($resp);
    };
};
