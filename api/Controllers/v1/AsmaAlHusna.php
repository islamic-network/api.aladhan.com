<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Models\AsmaAlHusna as AAH;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "An API to get the 99 beautiful names of God, in English and Arabic. All the endpoints return JSON and are available over `http` and `https`.",
        title: 'Asma Al Husna API - AlAdhan'
    ),
    servers: [
        new OA\Server(url: 'https://api.aladhan.com/v1'),
        new OA\Server(url: 'http://api.aladhan.com/v1')
    ],
    tags: [
        new OA\Tag(name: 'AsmaAlHusna'),
    ]
)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'AsmaAlHusnaResponseExample1',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: "الرَّحْمَنُ"),
                new OA\Property(property: 'transliteration', type: 'string', example: 'Ar Rahmaan'),
                new OA\Property(property: 'number', type: 'integer', example: 1),
                new OA\Property(property: 'en', properties: [
                    new OA\Property(property: 'meaning', type: 'string', example: 'The Beneficent')
                ],
                    type: 'object'),
            ],
        ),
        new OA\Schema(
            schema: 'AsmaAlHusnaResponseExample2',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: "الرَّحِيمُ"),
                new OA\Property(property: 'transliteration', type: 'string', example: 'Ar Raheem'),
                new OA\Property(property: 'number', type: 'integer', example: 2),
                new OA\Property(property: 'en', properties: [
                    new OA\Property(property: 'meaning', type: 'string', example: 'The Merciful')
                ],
                    type: 'object'),
            ],
        ),
        new OA\Schema(
            schema: 'AsmaAlHusnaResponseExample3',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: "الْمَلِكُ"),
                new OA\Property(property: 'transliteration', type: 'string', example: 'Al Malik'),
                new OA\Property(property: 'number', type: 'integer', example: 3),
                new OA\Property(property: 'en', properties: [
                    new OA\Property(property: 'meaning', type: 'string', example: 'The King / Eternal Lord')
                ],
                    type: 'object'),
            ],
        )
    ]
)]

class AsmaAlHusna extends Slim
{
    #[OA\Get(
        path: '/asmaAlHusna',
        description: 'Includes the Arabic text with transliteration and meaning of each name',
        summary: 'Get all the Asma Al Husna',
        tags: ['AsmaAlHusna'],
        responses: [
            new OA\Response(response: '200', description: 'Returns all Asma Al Husna',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    type: 'object',
                                    anyOf: [
                                        new OA\Schema(
                                            ref: '#/components/schemas/AsmaAlHusnaResponseExample1',
                                        ),
                                        new OA\Schema(
                                            ref: '#/components/schemas/AsmaAlHusnaResponseExample2',
                                        ),
                                        new OA\Schema(
                                            ref: '#/components/schemas/AsmaAlHusnaResponseExample3',
                                        ),

                                    ]
                                )
                            ),
                        ]
                    )
                )
            )
        ]
    )]

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $aah = new AAH();

        return Http\Response::json($response,
            $aah->get([]),
            200,
            true,
            604800,
            ['public']
        );
    }

    #[OA\Get(
        path: '/asmaAlHusna/{number}',
        description: 'Includes the Arabic text with transliteration and meaning',
        summary: 'Get one ore more Asma Al Husna',
        tags: ['AsmaAlHusna'],
        parameters: [
            new OA\PathParameter(name: 'number', description: 'A valid Asma Al Husna number or list of comma separated numbers between 1 and 99', in: 'path',
                required: true, schema: new OA\Schema(type: 'string'), example: '1 or 1,2,3'
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns a specific Asma Al Husna',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items:
                                new OA\Items(
                                    ref: '#/components/schemas/AsmaAlHusnaResponseExample1',
                                    type: 'object'
                                )
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(response: '404', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 404),
                            new OA\Property(property: 'status', type: 'string', example: 'NOT FOUND'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a valid number or list of comma separated numbers between 1 and 99')
                        ]
                    )
                )
            )
        ]
    )]

    public function getByNumber(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $aah = new AAH();

        $numbers = explode(',', Http\Request::getAttribute($request, 'number'));

        $r = $aah->get($numbers);

        if (empty($r)) {
            return Http\Response::json($response,
                'Please specify a valid number or list of comma separated numbers between 1 and 99',
                404,
                true,
                604800,
                ['public']
            );
        }

        return Http\Response::json($response,
            $r,
            200,
            true,
            604800,
            ['public']
        );
    }

}