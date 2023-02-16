<?php

use function Mamluk\Kipchak\env;

return [
    'enabled' => true,
    'pools' => [
        'cache' => [
            [
                'host' => env('MEMCACHED_HOST', 'memcached'),
                'port' => env('MEMCACHED_PORT', 11211),
            ]
        ],
    ],
    // Please see https://www.php.net/manual/en/memcached.constants.php for more details.
    // You can specify options for the Memcached PECL below and Kipchak will apply them as it bootstraps to the appropriate pool.
    'pools_options' => [
        'cache' => [
            Memcached::OPT_PREFIX_KEY => '',
            Memcached::OPT_CONNECT_TIMEOUT => 1000, // millisconeds
            Memcached::OPT_RETRY_TIMEOUT => 1, // seconds
            Memcached::OPT_POLL_TIMEOUT => 1000, // milliseconds
            Memcached::OPT_SERVER_FAILURE_LIMIT => 3,
            Memcached::OPT_REMOVE_FAILED_SERVERS => true,
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT
        ]
    ]
];

