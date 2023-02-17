<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Request as ApiRequest;
use Api\Models\PrayerTimes as PrayerTimesModel;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class PrayerTimesCalendar extends Slim
{
    public MemcachedAdapter $mc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mc = $this->container->get('cache.memcached.cache');
    }

    public function calendar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $year = Http\Request::getQueryParam($request, 'year');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $annual === true ? $month = 1 : $month = Http\Request::getQueryParam($request, 'month');
        if (Http\Request::getQueryParam($request, 'latitude') === null ||
            Http\Request::getQueryParam($request, 'longitude') === null ||
            $month === null ||
            $year === null) {
            throw new HttpBadRequestException($request, 'Please specify a latitude, longitude, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendar', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify a valid latitude and longitude.',
            400,
        );

    }

    public function calendarByAddress(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $year = Http\Request::getQueryParam($request, 'year');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $annual === true ? $month = 1 : $month = Http\Request::getQueryParam($request, 'month');
        if (Http\Request::getQueryParam($request, 'address') === null ||
            $month === null ||
            $year === null) {
            throw new HttpBadRequestException($request, 'Please specify an address, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByAddress', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify a city, country, month and year.',
            400,
        );
    }

    public function calendarByCity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $year = Http\Request::getQueryParam($request, 'year');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $annual === true ? $month = 1 : $month = Http\Request::getQueryParam($request, 'month');
        if (Http\Request::getQueryParam($request, 'city') === null ||
            Http\Request::getQueryParam($request, 'country') === null ||
            $month === null ||
            $year === null) {
            throw new HttpBadRequestException($request, 'Please specify a city, country, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByCity', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify a city, country, month and year.',
            400,
        );
    }


}