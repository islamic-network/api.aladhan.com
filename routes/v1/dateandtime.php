<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {

    $group->map(['GET', 'OPTIONS'],'/currentTime', [Controllers\v1\DateAndTime::class, 'time']);
    $group->map(['GET', 'OPTIONS'],'/currentDate', [Controllers\v1\DateAndTime::class, 'date']);
    $group->map(['GET', 'OPTIONS'],'/currentTimestamp', [Controllers\v1\DateAndTime::class, 'timestamp']);

});