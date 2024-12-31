<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */
$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/qibla/{latitude}/{longitude}', [Controllers\v1\Qibla::class, 'get']);
    $group->map(['GET', 'OPTIONS'],'/qibla/{latitude}/{longitude}/compass', [Controllers\v1\Qibla::class, 'getCompass']);
    $group->map(['GET', 'OPTIONS'],'/qibla/{latitude}/{longitude}/compass/{size}', [Controllers\v1\Qibla::class, 'getCompass']);
});