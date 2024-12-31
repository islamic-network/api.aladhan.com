<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use OpenApi\Attributes as OA;

class Methods extends Slim
{

    #[OA\Get(
        path: '/methods',
        description: 'Returns all the prayer times calculation methods & details supported by Islamic Network API.',
        summary: 'Prayer Times Methods',
        tags: ['Timings'],
        responses: [
            new OA\Response(response: '200', description: 'Returns all the prayer times calculation methods & details',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: 'MWL', ref: '#/components/schemas/PrayerCalMethodsResponse', type: 'object')
                                ], type: 'object'
                            ),
                        ]
                    )
                )
            )
        ]
    )]

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $pt = new PrayerTimes();

        return Http\Response::json($response,
            $pt->getMethods(),
            200,
            true,
            604800,
            ['public']
        );
    }

}