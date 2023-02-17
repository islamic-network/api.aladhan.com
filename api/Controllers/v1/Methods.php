<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use IslamicNetwork\PrayerTimes\PrayerTimes;

class Methods extends Slim
{

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $pt = new PrayerTimes();

        return Http\Response::json($response,
            $pt->getMethods(),
            200,
            true,
            604800
        );
    }

}