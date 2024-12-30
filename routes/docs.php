<?php
use Api\Controllers;

/**
 * @var \Slim\App $app
 */

$app->get('/docs', [Controllers\Docs::class, 'generate']);