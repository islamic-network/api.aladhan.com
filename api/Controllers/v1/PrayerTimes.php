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

class PrayerTimes extends Slim
{
    public MemcachedAdapter $mc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mc = $this->container->get('cache.memcached.cache');
    }

    public function timings(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'latitude') === null || Http\Request::getQueryParam($request, 'longitude') === null) {
            throw new HttpBadRequestException($request, 'Please specify a latitude and longitude.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);
        $datestring = Http\Request::getAttribute($request, 'date');

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/timings/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'timings');

            return Http\Response::json($response,
                ['timings' => $r[0], 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
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

    public function timingsByAddress(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'address') === null) {
            throw new HttpBadRequestException($request, 'Please specify an address.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);
        $datestring = Http\Request::getAttribute($request, 'date');

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/timingsByAddress/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {

            $r = $ptm->respond($datestring, 'timingsByAddress');

            return Http\Response::json($response,
                ['timings' => $r[0], 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify a valid address.',
            400,
        );
    }

    public function timingsByCity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'city') === null || Http\Request::getQueryParam($request, 'country') === null) {
            throw new HttpBadRequestException($request, 'Please specify a city and country.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);
        $datestring = Http\Request::getAttribute($request, 'date');

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/timingsByCity/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'timingsByCity');

            return Http\Response::json($response,
                ['timings' => $r[0], 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify a valid city and country.',
            400,
        );
    }

    public function nextPrayer(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'latitude') === null || Http\Request::getQueryParam($request, 'longitude') === null) {
            throw new HttpBadRequestException($request, 'Please specify a latitude and longitude.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);
        $datestring = Http\Request::getAttribute($request, 'date');
        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/nextPrayer/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'nextPrayer');

            $nextPrayer = PrayerTimesHelper::nextPrayerTime($r[2], $r[3], $ptm->latitude, $ptm->longitude, $ptm->latitudeAdjustmentMethod, $ptm->iso8601, $ptm->timezone);

            return Http\Response::json($response,
                ['timings' => $nextPrayer, 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
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

    public function nextPrayerByAddress(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (Http\Request::getQueryParam($request, 'address') === null) {
            throw new HttpBadRequestException($request, 'Please specify an address.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);
        $datestring = Http\Request::getAttribute($request, 'date');
        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/nextPrayerByAddress/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'nextPrayerByAddress');

            $nextPrayer = PrayerTimesHelper::nextPrayerTime($r[2], $r[3], $ptm->latitude, $ptm->longitude, $ptm->latitudeAdjustmentMethod, $ptm->iso8601, $ptm->timezone);

            return Http\Response::json($response,
                ['timings' => $nextPrayer, 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
                200,
                true,
                604800
            );
        }

        return Http\Response::json($response,
            'Please specify an address.',
            400,
        );

    }

}