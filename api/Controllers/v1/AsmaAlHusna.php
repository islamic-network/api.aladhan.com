<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Models\AsmaAlHusna as AAH;
use OpenApi\Attributes as OA;

class AsmaAlHusna extends Slim
{
    #[OA\Get(
        path: '/asmaAlHusna',
        description: 'Returns a list of all Asma Al Husna along with details',
        summary: 'Asma Al Husna',
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
                                    oneOf: [
                                        new OA\Schema(
                                            ref: '#/components/schemas/AsmaAlHusnaResponse',
                                        ),
                                        new OA\Schema(
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
                                            properties: [
                                                new OA\Property(property: 'name', type: 'string', example: "الْمَلِكُ"),
                                                new OA\Property(property: 'transliteration', type: 'string', example: 'Al Malik'),
                                                new OA\Property(property: 'number', type: 'integer', example: 3),
                                                new OA\Property(property: 'en', properties: [
                                                    new OA\Property(property: 'meaning', type: 'string', example: 'The King / Eternal Lord')
                                                ],
                                                    type: 'object'),
                                            ],
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
        description: 'Returns a requested Asma Al Husna with its details',
        summary: 'A specific Asma Al Husna',
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
                                items: new OA\Items(
                                    ref: '#/components/schemas/AsmaAlHusnaResponse',
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