<?php

use function Mamluk\Kipchak\env;

return [
    'name' => 'aladhan-api', // Hyphen or underscore separated string
    'debug' => false, // (bool) env('DEBUG', true),
    // If debug is enabled, loglevel is debug. Otherwise, it is info. Overwrite it by specifying it below.
    // 'loglevel' => \Monolog\Level::Debug
];