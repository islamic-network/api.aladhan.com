<?php

namespace Api\Controllers\v1;

use Api\Models\HijriCalendar;
use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Request;
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
        $adjustment = Http\Request::getQueryParam($request, 'adjustment') === null ? 0 : (int) Http\Request::getQueryParam($request, 'adjustment');
        $qyear = Http\Request::getQueryParam($request, 'year') === null ?  Request::calendarGetQYear($hijri, $this->hc) :  Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month') === null ? Request::calendarGetQMonth($hijri, $this->hc, $adjustment) : Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $year = Http\Request::getAttribute($request, 'year');
        $month = Http\Request::getAttribute($request, 'month');

        if ($year === null && $month === null) {
            // This is a legacy
            $url = Request::getCalendarRedirectableUrl($hijri, $annual, (int) $qyear, (int) $qmonth);

            return Http\Response::redirect($response, $url . http_build_query($request->getQueryParams()), 302);
        }

        if ($month === null) {
            $annual = true;
            $month = 1; // any value will do
        }

        if (Http\Request::getQueryParam($request, 'latitude') === null ||
            Http\Request::getQueryParam($request, 'longitude') === null ||
            !Request::isYearValid($year) ||
            !Request::isMonthValid($month)) {
            throw new HttpBadRequestException($request, 'Please specify a latitude, longitude, year and/or year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendar', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
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
        $adjustment = Http\Request::getQueryParam($request, 'adjustment') === null ? 0 : (int) Http\Request::getQueryParam($request, 'adjustment');
        $qyear = Http\Request::getQueryParam($request, 'year') === null ?  Request::calendarGetQYear($hijri, $this->hc) :  Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month') === null ? Request::calendarGetQMonth($hijri, $this->hc, $adjustment) : Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $year = Http\Request::getAttribute($request, 'year');
        $month = Http\Request::getAttribute($request, 'month');

        if ($year === null && $month === null) {
            // This is a legacy
            $url = Request::getCalendarRedirectableUrl($hijri, $annual, (int) $qyear, (int) $qmonth, "ByAddress");

            return Http\Response::redirect($response, $url . http_build_query($request->getQueryParams()), 302);
        }

        if ($month === null) {
            $annual = true;
            $month = 1; // any value will do
        }

        if (Http\Request::getQueryParam($request, 'address') === null ||
            !Request::isMonthValid($month) ||
            !Request::isYearValid($year)) {
            throw new HttpBadRequestException($request, 'Please specify an address, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByAddress', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
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
        $adjustment = Http\Request::getQueryParam($request, 'adjustment') === null ? 0 : (int) Http\Request::getQueryParam($request, 'adjustment');
        $qyear = Http\Request::getQueryParam($request, 'year') === null ?  Request::calendarGetQYear($hijri, $this->hc) :  Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month') === null ? Request::calendarGetQMonth($hijri, $this->hc, $adjustment) : Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $year = Http\Request::getAttribute($request, 'year');
        $month = Http\Request::getAttribute($request, 'month');

        if ($year === null && $month === null) {
            // This is a legacy
            $url = Request::getCalendarRedirectableUrl($hijri, $annual, (int) $qyear, (int) $qmonth, "ByCity");

            return Http\Response::redirect($response, $url . http_build_query($request->getQueryParams()), 302);
        }

        if ($month === null) {
            $annual = true;
            $month = 1; // any value will do
        }

        if (Http\Request::getQueryParam($request, 'city') === null ||
            Http\Request::getQueryParam($request, 'country') === null ||
            !Request::isMonthValid($month) ||
            !Request::isYearValid($year)) {
            throw new HttpBadRequestException($request, 'Please specify a city, country, month and year.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByCity', $hijri);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Please specify a city, country, month and year.',
            400,
        );
    }


}