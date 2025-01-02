<?php

use Api\Controllers;

/**
 * @var \Slim\App $app
 */

$app->get('/documentation/openapi/yaml', [Controllers\Documentation::class, 'generate']);
$app->get('/documentation/openapi/prayer-times/yaml', [Controllers\Documentation::class, 'generate']);
$app->get('/documentation/openapi/islamic-calendar/yaml', [Controllers\Documentation::class, 'generate']);
$app->get('/documentation/openapi/qibla/yaml', [Controllers\Documentation::class, 'generate']);
$app->get('/documentation/openapi/asma-al-husna/yaml', [Controllers\Documentation::class, 'generate']);