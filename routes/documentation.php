<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App $app
 */

$app->group('/v1/documentation/openapi', function(RouteCollectorProxy $group) {
    $group->get('/yaml', [Controllers\v1\Documentation\Documentation::class, 'generate']);
    $group->get('/prayer-times/yaml', [Controllers\v1\Documentation\Documentation::class, 'generate']);
    $group->get('/islamic-calendar/yaml', [Controllers\v1\Documentation\Documentation::class, 'generate']);
    $group->get('/qibla/yaml', [Controllers\v1\Documentation\Qibla::class, 'generate']);
    $group->get('/asma-al-husna/yaml', [Controllers\v1\Documentation\AsmaAlHusna::class, 'generate']);
    $group->get('/date-time/yaml', [Controllers\v1\Documentation\Documentation::class, 'generate']);
    $group->get('/geo/yaml', [Controllers\v1\Documentation\Documentation::class, 'generate']);
});