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

class Geo extends Slim
{
    public MemcachedAdapter $mc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
         $this->mc = $this->container->get('cache.memcached.cache');
        // $this->mc = $this->container->get('cache.apcu.cache');

    }

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