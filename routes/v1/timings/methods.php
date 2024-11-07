<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/methods', [Controllers\v1\Methods::class, 'get']);

});