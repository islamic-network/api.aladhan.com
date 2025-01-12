<?php

use function Mamluk\Kipchak\env;

return [
    'name' => 'aladhan-api', // Hyphen or underscore separated string
    'debug' => env('DEBUG', false) === "true",
    'logExceptions' => true,
    'logExceptionDetails' => false,
    // If debug is enabled, loglevel is debug. Otherwise, it is info. Overwrite it by specifying it below.
    // 'loglevel' => \Monolog\Level::Debug
];