<?php

namespace Api\Controllers;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi as OApi;
use Api\Utils\Response;
use Mamluk\Kipchak\Components\Http;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "A RESTful API to get islamic prayer times for a given day, month or year.
    The API has several endpoints to assist developers. All the endpoints return JSON and are available over `http` and `https`.
    Please note that prayer timings might not always match your local mosque or government authority. Their timings are likely tuned or adjusted. Please see `https://aladhan.com/calculation-methods` for more details.",
        title: 'AlAdhan - Prayer Times API'
    ),
    servers: [
        new OA\Server(url: 'http://api.aladhan.com'),
        new OA\Server(url: 'https://api.aladhan.com')
    ],
    security: [
        ['bearerAuth' => []]
    ],
    tags: [
        new OA\Tag(name: 'AsmaAlHusna'),
        new OA\Tag(name: 'Hijri')
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
        ),
        new OA\Schema(
            schema: 'HijriCalendarDateResponse',
            properties: [
                new OA\Property(property: 'hijri',
                    properties: [
                        new OA\Property(property: 'date', type: 'string', example: '01-04-1439'),
                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                        new OA\Property(property: 'day', type: 'integer', example: 1),
                        new OA\Property(property: 'weekday', properties: [
                            new OA\Property(property: 'en', type: 'string', example: 'Al Thalaata'),
                            new OA\Property(property: 'ar', type: 'string', example: "الثلاثاء"),
                        ],type: 'object'),
                        new OA\Property(property: 'month', properties: [
                            new OA\Property(property: 'number', type: 'integer', example: 4),
                            new OA\Property(property: 'en', type: 'string', example: "Rabīʿ al-thānī"),
                            new OA\Property(property: 'ar', type: 'string', example: "رَبيع الثاني"),
                            new OA\Property(property: 'days', type: 'integer', example: 30)
                        ], type: 'object'),
                        new OA\Property(property: 'year', type: 'integer', example: 1439),
                        new OA\Property(property: 'designation', properties: [
                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AH'),
                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Hegirae'),
                        ],type: 'object'),
                        new OA\Property(property: 'holidays', type: 'array', items: new OA\Items(), example: []),
                        new OA\Property(property: 'method', type: 'string', example: 'HJCoSA')
                    ], type: 'object'),

                new OA\Property(property: 'gregorian',
                    properties: [
                        new OA\Property(property: 'date', type: 'string', example: '19-12-2017'),
                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                        new OA\Property(property: 'day', type: 'integer', example: 19),
                        new OA\Property(property: 'weekday', properties: [
                            new OA\Property(property: 'en', type: 'string', example: 'Tuesday')
                        ],type: 'object'),
                        new OA\Property(property: 'month', properties: [
                            new OA\Property(property: 'number', type: 'integer', example: 12),
                            new OA\Property(property: 'en', type: 'string', example: "December"),
                        ], type: 'object'),
                        new OA\Property(property: 'year', type: 'integer', example: 2017),
                        new OA\Property(property: 'designation', properties: [
                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AD'),
                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Domini'),
                        ],type: 'object'),
                    ], type: 'object'),
            ]
        )
    ],
    parameters: [
        new OA\Parameter(parameter: 'HijriMonth', name: 'month', description: 'Month number as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 4),
        new OA\Parameter(parameter: 'HijriYear', name: 'year', description: 'Year as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1439),
        new OA\Parameter(parameter: 'GregorianYear', name: 'year', description: 'Year as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 2018),
    ],
    securitySchemes: [
        new OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', description: 'The field only accepts JWT or PAT tokens.', scheme: 'bearer')
    ]
)]
class Docs
{
    public function generate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $openApi = OApi\Generator::scan(['/var/www/api/']);
//        header('Content-Type: application/x-yaml');

//        return Http\Response::json($response, $openApi->toYaml(), 200);
        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'application/yaml']);
    }
}