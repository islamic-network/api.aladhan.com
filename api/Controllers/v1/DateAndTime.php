<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Timezone;
use DateTime;
use DateTimeZone;
use OpenApi\Attributes as OA;

class DateAndTime extends Slim
{

    #[OA\Get(
        path: '/currentTime',
        description: 'Returns Current time based on the timezone',
        summary: 'Current time',
        tags: ['DateAndTime'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/TimeZoneQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Current time based on the timezone',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'string', example: '14:44'),
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400DateTimeResponse', response: '400')
        ]
    )]

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

    #[OA\Get(
        path: '/currentDate',
        description: 'Returns Current date based on the timezone',
        summary: 'Current date',
        tags: ['DateAndTime'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/TimeZoneQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Current date based on the timezone',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'string', example: '31-12-2024'),
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400DateTimeResponse', response: '400')
        ]
    )]

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

    #[OA\Get(
        path: '/currentTimestamp',
        description: 'Returns Current timestamp based on the timezone',
        summary: 'Current timestamp',
        tags: ['DateAndTime'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/TimeZoneQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Current timestamp based on the timezone',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'string', example: '1735650002'),
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400DateTimeResponse', response: '400')
        ]
    )]

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