<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Request as ApiRequest;
use Api\Utils\PrayerTimesHelper;
use Api\Models\PrayerTimes as PrayerTimesModel;
use DateTimeZone;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use OpenApi\Attributes as OA;

class Geo extends Slim
{
    public MemcachedAdapter $mc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mc = $this->container->get('cache.memcached.cache');

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
            604800,
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
            604800,
            ['public']
        );
    }


}