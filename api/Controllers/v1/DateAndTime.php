<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Timezone;
use DateTime;
use DateTimeZone;

class DateAndTime extends Slim
{

    public function time(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $zone = Http\Request::getQueryParam($request, 'zone');
        if ($zone === null || !Timezone::isTimeZoneValid($zone)) {
            return Http\Response::json($response,
                'Please specify a valid timezone. Example: Europe/London',
                400
            );
        }

        $date = new DateTime('now', new DateTimeZone($zone));

        return Http\Response::json($response,
            $date->format('H:i'),
            200
        );
    }
    public function date(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $zone = Http\Request::getQueryParam($request, 'zone');
        if ($zone === null || !Timezone::isTimeZoneValid($zone)) {
            return Http\Response::json($response,
                'Please specify a valid timezone. Example: Europe/London',
                400
            );
        }

        $date = new DateTime('now', new DateTimeZone($zone));

        return Http\Response::json($response,
            $date->format('d-m-Y'),
            200
        );
    }

    public function timestamp(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $zone = Http\Request::getQueryParam($request, 'zone');
        if ($zone === null || !Timezone::isTimeZoneValid($zone)) {
            return Http\Response::json($response,
                'Please specify a valid timezone. Example: Europe/London',
                400
            );
        }

        $date = new DateTime('now', new DateTimeZone($zone));

        return Http\Response::json($response,
            $date->format('U'),
            200
        );
    }

}