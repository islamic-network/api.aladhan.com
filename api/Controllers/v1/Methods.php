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
        description: 'Returns all the prayer times calculation methods & details supported by Islamic Network API',
        summary: 'Prayer Times Methods',
        tags: ['Prayer Time Methods'],
        responses: [
            new OA\Response(response: '200', description: 'Returns all the prayer times calculation methods & details',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200PrayerCalMethodsResponse')
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
            7200,
            ['public']
        );
    }

}