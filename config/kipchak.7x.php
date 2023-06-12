<?php

use function Mamluk\Kipchak\env;

return [
    'apikey' =>  env('X7X_API_KEY', 'cf0561832a24e55fedaf201db7f1c2d2'),
    'geocode_baseurl' => env('X7X_GEOCODE_BASEURL', 'https://api.7x.ax'),
    'timezone_baseurl' => env('X7X_TIMEZONE_BASEURL', 'https://api.7x.ax'),
];