<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlQibla\Calculation;

class Qibla extends Slim
{

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $latitude = floatval($request->getAttribute('latitude'));
        $longitude = floatval($request->getAttribute('longitude'));
        $calculation = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'direction' => Calculation::get($latitude, $longitude)
        ];

        return Http\Response::json($response,
            $calculation,
            200,
            true,
            604800,
            ['public']
        );
    }

}