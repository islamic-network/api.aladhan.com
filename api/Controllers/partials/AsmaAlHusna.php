<?php
namespace Api\Controllers\partials;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "A RESTful API to get islamic prayer times for a given day, month or year.
    The API has several endpoints to assist developers. All the endpoints return JSON and are available over `http` and `https`.
    Please note that prayer timings might not always match your local mosque or government authority. Their timings are likely tuned or adjusted. Please see `https://aladhan.com/calculation-methods` for more details.",
        title: 'AlAdhan - Prayer Times Asma-Al-Husna API'
    ),
    servers: [
        new OA\Server(url: 'http://api.aladhan.com'),
        new OA\Server(url: 'https://api.aladhan.com')
    ],
    tags: [
        new OA\Tag(name: 'AsmaAlHusna')
    ]

)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'AsmaAlHusnaResponse',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: "الرَّحْمَنُ"),
                new OA\Property(property: 'transliteration', type: 'string', example: 'Ar Rahmaan'),
                new OA\Property(property: 'number', type: 'integer', example: 1),
                new OA\Property(property: 'en', properties: [
                    new OA\Property(property: 'meaning', type: 'string', example: 'The Beneficent')
                ],
                    type: 'object'),
            ],
        )
    ]
)]
class AsmaAlHusna
{

}