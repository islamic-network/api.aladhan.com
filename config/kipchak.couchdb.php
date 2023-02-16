<?php

use function Mamluk\Kipchak\env;

return [
    'enabled' => false,
    'connections' => [
        'default' => [
            'host' => env('COUCHDB_HOST', 'http://couchdb'), # No trailing slash, please.
            'port' => getenv('COUCHDB_PORT') !== false ? (int) getenv('COUCHDB_PORT') : null,
            'username' => env('COUCHDB_USER', 'api'),
            'password' => env('COUCHDB_PASSWORD', 'api'),
            'database' => env('COUCHDB_DATABASE', 'api_database')
        ]
    ]
];
