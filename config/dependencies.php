<?php
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Model\Locations;
use AlAdhanApi\Handler\AlAdhanHandler;
use AlAdhanApi\Handler\AlAdhanNotFoundHandler;

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

/** Invoke Middleware for WAF Checks */
$app->add(function (Request $request, Response $response, $next) {

    $proxyMode = (bool)getenv('WAF_PROXY_MODE');

    if ($proxyMode) {
        // Validate Key
        if (isset($request->getHeader('X-WAF-KEY')[0]) && $request->getHeader('X-WAF-KEY')[0] === getenv('WAF_KEY')) {
            $response = $next($request, $response);

            return $response;
        }

        throw new \Quran\Exception\WafKeyMismatchException();
    }

    $response = $next($request, $response);

    return $response;

});
