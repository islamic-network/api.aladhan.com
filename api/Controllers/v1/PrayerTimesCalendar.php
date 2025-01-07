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
use OpenApi\Attributes as OA;


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

    #[OA\Get(
        path: '/hijriCalendar/{year}',
        description: 'Returns Prayer times for a  Hijri year',
        summary: 'Prayer times for a Hijri year',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri year',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendar/{year}/{month}',
        description: 'Returns Prayer times for a requested Hijri month',
        summary: 'Prayer times for a Hijri month',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri month',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendar/{year}',
        description: 'Returns Prayer times for a Gregorian year',
        summary: 'Prayer times for a Gregorian year',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian year',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendar/{year}/{month}',
        description: 'Returns Prayer times for a Gregorian month',
        summary: 'Prayer times for a Gregorian month',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian month',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]
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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendar', $hijri, 604800, false);

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

    #[OA\Get(
        path: '/hijriCalendarByAddress/{year}',
        description: 'Returns Prayer times for a Hijri year for an address',
        summary: 'Prayer times for a Hijri year for an address',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri year for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendarByAddress/{year}/{month}',
        description: 'Returns Prayer times for a Hijri month for an address',
        summary: 'Prayer times for a Hijri month for an address',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri month for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByAddress/{year}',
        description: 'Returns Prayer times for a Gregorian year for an address',
        summary: 'Prayer times for a Gregorian year for an address',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian year for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByAddress/{year}/{month}',
        description: 'Returns Prayer times for a Gregorian month for an address',
        summary: 'Prayer times for a Gregorian month for an address',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian month for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]
    public function calendarByAddress(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $adjustment = Http\Request::getQueryParam($request, 'adjustment') === null ? 0 : (int) Http\Request::getQueryParam($request, 'adjustment');
        $qyear = Http\Request::getQueryParam($request, 'year') === null ?  Request::calendarGetQYear($hijri, $this->hc) :  Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month') === null ? Request::calendarGetQMonth($hijri, $this->hc, $adjustment) : Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $year = Http\Request::getAttribute($request, 'year');
        $month = Http\Request::getAttribute($request, 'month');
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;

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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByAddress', $hijri, 604800, $enableMasking);

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

    #[OA\Get(
        path: '/hijriCalendarByCity/{year}',
        description: 'Returns Prayer times a Hijri year for a city and country',
        summary: 'Prayer times for a Hijri year for a city and country',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Hijri year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendarByCity/{year}/{month}',
        description: 'Returns Prayer times a Hijri month for a city and country',
        summary: 'Prayer times for a Hijri month for a city and country',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Hijri month for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByCity/{year}',
        description: 'Returns Prayer times a Gregorian year for a city and country',
        summary: 'Prayer times for a Gregorian year for a city and country',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Gregorian year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByCity/{year}/{month}',
        description: 'Returns Prayer times a Gregorian month for a city and country',
        summary: 'Prayer times for a Gregorian month for a city and country',
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Gregorian year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimingsPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    public function calendarByCity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $hijri = str_contains($request->getUri(), 'hijri');
        $adjustment = Http\Request::getQueryParam($request, 'adjustment') === null ? 0 : (int) Http\Request::getQueryParam($request, 'adjustment');
        $qyear = Http\Request::getQueryParam($request, 'year') === null ?  Request::calendarGetQYear($hijri, $this->hc) :  Http\Request::getQueryParam($request, 'year');
        $qmonth = Http\Request::getQueryParam($request, 'month') === null ? Request::calendarGetQMonth($hijri, $this->hc, $adjustment) : Http\Request::getQueryParam($request, 'month');
        $annual = Http\Request::getQueryParam($request, 'annual') === "true";
        $year = Http\Request::getAttribute($request, 'year');
        $month = Http\Request::getAttribute($request, 'month');
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;

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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByCity', $hijri, 604800, $enableMasking);

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