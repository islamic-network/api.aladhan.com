<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Database;


$container = $app->getContainer();

$container['helper'] = function($c) {
    $helper = new \stdClass();
    $helper->logger = new Log();
    $helper->database = new Database($helper->logger);

    return $helper;
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
