<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {

    $group->map(['GET', 'OPTIONS'],'/cityInfo', [Controllers\v1\Geo::class, 'city']);
    $group->map(['GET', 'OPTIONS'],'/addressInfo', [Controllers\v1\Geo::class, 'address']);

});