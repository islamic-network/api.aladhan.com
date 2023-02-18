<?php

namespace Api\Controllers\v1;

use Api\Models\HijriCalendar;
use Api\Utils\Request;
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
    public HijriCalendar $hc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mc = $this->container->get('cache.memcached.cache');
        $this->hc = new HijriCalendar();
    }

    public function calendar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $qyear = Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        if ($qyear === null) {
            if ($hijri) {
                $qyear = $this->hc->getCurrentIslamicYear();
            } else {
                $qyear = date('Y');
            }
        }
        if ($qmonth === null) {
            $qmonth = date('n');
        }

        $month = Http\Request::getAttribute($request, 'month');
        $year = Http\Request::getAttribute($request, 'year');

        if ($month === null || $year === null) {
            if ($hijri) {
                return Http\Response::redirect($response, "/v1/hijriCalendar/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            } else {
                return Http\Response::redirect($response, "/v1/calendar/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            }
        }

        if (Http\Request::getQueryParam($request, 'latitude') === null ||
            Http\Request::getQueryParam($request, 'longitude') === null ||
            !Request::isMonthValid($month) ||
            !Request::isYearValid($year)) {
            throw new HttpBadRequestException($request, 'Please specify a latitude, longitude, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendar', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600
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
        $qyear = Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        if ($qyear === null) {
            if ($hijri) {
                $qyear = $this->hc->getCurrentIslamicYear();
            } else {
                $qyear = date('Y');
            }
        }
        if ($qmonth === null) {
            $qmonth = date('n');
        }

        $month = Http\Request::getAttribute($request, 'month');
        $year = Http\Request::getAttribute($request, 'year');

        if ($month === null || $year === null) {
            if ($hijri) {
                return Http\Response::redirect($response, "/v1/hijriCalendarByAddress/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            } else {
                return Http\Response::redirect($response, "/v1/calendarByAddress/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            }
        }

        if (Http\Request::getQueryParam($request, 'address') === null ||
            !Request::isMonthValid($month) ||
            !Request::isYearValid($year)) {
            throw new HttpBadRequestException($request, 'Please specify an address, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByAddress', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600
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
        $qyear = Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        if ($qyear === null) {
            if ($hijri) {
                $qyear = $this->hc->getCurrentIslamicYear();
            } else {
                $qyear = date('Y');
            }
        }
        if ($qmonth === null) {
            $qmonth = date('n');
        }

        $month = Http\Request::getAttribute($request, 'month');
        $year = Http\Request::getAttribute($request, 'year');

        if ($month === null || $year === null) {
            if ($hijri) {
                return Http\Response::redirect($response, "/v1/hijriCalendarByCity/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            } else {
                return Http\Response::redirect($response, "/v1/calendarByCity/$qmonth/$qyear?" . http_build_query($request->getQueryParams()), 302);
            }        }

        if (Http\Request::getQueryParam($request, 'city') === null ||
            Http\Request::getQueryParam($request, 'country') === null ||
            !Request::isMonthValid($month) ||
            !Request::isYearValid($year)) {
            throw new HttpBadRequestException($request, 'Please specify a city, country, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (ApiRequest::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByCity', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600
            );
        }

        return Http\Response::json($response,
            'Please specify a city, country, month and year.',
            400,
        );
    }


}