<?php
use Api\Controllers;

/**
 * @var \Slim\App $app
 */

$app->get('/documentation/openapi/yaml', [Controllers\Docs::class, 'generate']);