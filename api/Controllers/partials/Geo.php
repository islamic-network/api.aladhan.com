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
        title: 'AlAdhan - Prayer Times Geo API'
    ),
    servers: [
        new OA\Server(url: 'http://api.aladhan.com'),
        new OA\Server(url: 'https://api.aladhan.com')
    ],
    tags: [
        new OA\Tag(name: 'Geo')
    ]
)]

#[OA\Components(
    responses: [
        new OA\Response(response: '200GeoResponse', description: 'Returns City information for the given city and country',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 200),
                        new OA\Property(property: 'status', type: 'string', example: 'OK'),
                        new OA\Property(property: 'data',
                            properties: [
                                new OA\Property(property: 'latitude', type: 'number', example: 51.509648),
                                new OA\Property(property: 'longitude', type: 'number', example: -0.099076),
                                new OA\Property(property: 'timezone', type: 'string', example: "Europe/London")
                            ], type: 'object'
                        )
                    ]
                )
            )
        ),
    ],
    parameters: [
        new OA\QueryParameter(parameter: 'TimingsAddressQueryParameter', name: 'address', description: 'Address of user location',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '?address=A5204, London'),
        new OA\QueryParameter(parameter: 'TimingsCityQueryParameter', name: 'city', description: 'Name of the city',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '?city=London'),
        new OA\QueryParameter(parameter: 'TimingsCountryQueryParameter',name: 'country', description: 'A country name or 2 character alpha ISO 3166 code',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '&country=GB'),
        new OA\QueryParameter(parameter: '7xAPIKeyQueryParameter', name: 'x7xapikey', description: '7x API Key', in: 'query',
            required: false, schema: new OA\Schema(type: 'string'), example: '&x7xapikey=P244d623e2fe2daf56359fxxxxx')
    ]
)]
class Geo
{

}