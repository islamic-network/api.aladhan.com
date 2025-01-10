<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Models\PrayerTimes as PrayerTimesModel;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "Geocoding APIs",
        title: 'Geocoding APIs - AlAdhan'
    ),
    servers: [
        new OA\Server(url: 'https://api.aladhan.com/v1'),
        new OA\Server(url: 'http://api.aladhan.com/v1')
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
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'A5204, London'),
        new OA\QueryParameter(parameter: 'TimingsCityQueryParameter', name: 'city', description: 'Name of the city',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'London'),
        new OA\QueryParameter(parameter: 'TimingsCountryQueryParameter',name: 'country', description: 'A country name or 2 character alpha ISO 3166 code',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'GB'),
        new OA\QueryParameter(parameter: '7xAPIKeyQueryParameter', name: 'x7xapikey', description: '7x API Key - An API key from <a href="https://7x.ax" target="_blank">https://7x.ax</a> to geocode the address, city and country. 
        If you do not provide one the response will mask the geocoded co-ordinates.
        ', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'P244d623e2fe2daf56359fxxxxx')
    ]
)]
class Geo extends Slim
{
    public MemcachedAdapter $mc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
         $this->mc = $this->container->get('cache.memcached.cache');
        // $this->mc = $this->container->get('cache.apcu.cache');

    }

    #[OA\Get(
        path: '/addressInfo',
        description: 'Returns address information like latitude, longitude and timezone for the requested address',
        summary: 'Address information for requested address',
        tags: ['Geo'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/200GeoResponse', response: '200',
                description: 'Returns Address information for the given address'
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'integer', example: 'Please specify an address.')
                        ],
                    )
                )
            )
        ]
    )]

    public function address(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'address') === null) {
            throw new HttpBadRequestException($request, 'Please specify an address.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        return Http\Response::json($response,
            ['latitude' => $ptm->latitude, 'longitude' => $ptm->longitude, 'timezone' => $ptm->timezone],
            200,
            true,
            7200,
            ['public']
        );

    }

    #[OA\Get(
        path: '/cityInfo',
        description: 'Returns city information like latitude, longitude and timezone for the requested city',
        summary: 'City information for requested city',
        tags: ['Geo'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/200GeoResponse', response: '200',
                description: 'Returns City information for the given city and country.'
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'integer', example: 'Please specify a city and country.')
                        ],
                    )
                )
            )
        ]
    )]

    public function city(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'city') === null || Http\Request::getQueryParam($request, 'country') === null) {
            throw new HttpBadRequestException($request, 'Please specify a city and country.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        return Http\Response::json($response,
            ['latitude' => $ptm->latitude, 'longitude' => $ptm->longitude, 'timezone' => $ptm->timezone],
            200,
            true,
            7200,
            ['public']
        );
    }


}