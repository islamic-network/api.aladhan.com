<?php
/**
 * Tweak these headers as needed, especially if you are going to use this API from a client side SPA
 * without a Backend for Frontend
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Authorization, origin');

require_once (realpath(__DIR__ . '/../vendor/autoload.php'));

use Mamluk\Kipchak\Api;

// Instantiate Slim, load dependencies and middlewares
$app = Api::boot();

$app->addBodyParsingMiddleware();

$app->run();
