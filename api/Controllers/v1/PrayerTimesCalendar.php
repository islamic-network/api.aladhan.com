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
use DateTime;


class PrayerTimesCalendar extends Slim
{
    public MemcachedAdapter $mc;
    public HijriCalendar $hc;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->mc = $this->container->get('cache.memcached.cache');
        // $this->mc = $this->container->get('cache.apcu.cache');
        $this->hc = new HijriCalendar();
    }

    #[OA\Get(
        path: '/hijriCalendar/{year}',
        description: 'Returns Prayer times for a Hijri year',
        summary: 'Prayer times for a Hijri year',
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri year',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/hijriCalendar/{year}/{month}',
        description: 'Returns Prayer times for a requested Hijri month',
        summary: 'Prayer times for a Hijri month',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri month',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendar/{year}',
        description: 'Returns Prayer times for a Gregorian year',
        summary: 'Prayer times for a Gregorian year',
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian year',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendar/{year}/{month}',
        description: 'Returns Prayer times for a Gregorian month',
        summary: 'Prayer times for a Gregorian month',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian month',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendar', $hijri, 7200, false);

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
        path: '/calendar/from/{start}/to/{end}',
        description: 'Returns a prayer times calendar for a date range between a start date and end date with a maximum date range difference of 11 months',
        summary: 'Prayer times calendar between 2 dates',
        tags: ['Date Range Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/start'),
            new OA\PathParameter(ref: '#/components/parameters/end'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for the given date range between the start date and end date',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a latitude, longitude, start date and end date and ensure that the end date is after the start date.')
                        ], type: 'object'
                    )
                )
            )
        ]
    )]
    public function calendarByRange(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $startDate = Http\Request::getAttribute($request, 'start');
        $endDate = Http\Request::getAttribute($request, 'end');

        if (Http\Request::getQueryParam($request, 'latitude') === null ||
            Http\Request::getQueryParam($request, 'longitude') === null ||
            !Request::areStartAndEndDateValid($startDate, $endDate)) {
            throw new HttpBadRequestException($request, 'Please specify a latitude, longitude, start date and end date and ensure that the end date is after the start date.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendarByRange(DateTime::createFromFormat('j-n-Y', $startDate), DateTime::createFromFormat('j-n-Y', $endDate), 'calendarByRange', 7200, false);

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
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri year for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/hijriCalendarByAddress/{year}/{month}',
        description: 'Returns Prayer times for a Hijri month for an address',
        summary: 'Prayer times for a Hijri month for an address',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Hijri month for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendarByAddress/{year}',
        description: 'Returns Prayer times for a Gregorian year for an address',
        summary: 'Prayer times for a Gregorian year for an address',
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian year for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendarByAddress/{year}/{month}',
        description: 'Returns Prayer times for a Gregorian month for an address',
        summary: 'Prayer times for a Gregorian month for an address',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times for a Gregorian month for an address',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByAddress', $hijri, 7200, $enableMasking);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Please specify an address, month and year.',
            400,
        );
    }

    #[OA\Get(
        path: '/calendarByAddress/from/{start}/to/{end}',
        description: 'Returns a prayer times calendar for an address for a given date range between start date and end date with a maximum date range difference of 11 months',
        summary: 'Prayer times calendar for an address between 2 dates',
        tags: ['Date Range Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/start'),
            new OA\PathParameter(ref: '#/components/parameters/end'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an address as per the given date range between start date and end date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify an address, start date and end date and ensure that the end date is after the start date.')
                        ], type: 'object'
                    )
                )
            )
        ]
    )]
    public function calendarByAddressByRange(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;
        $startDate = Http\Request::getAttribute($request, 'start');
        $endDate = Http\Request::getAttribute($request, 'end');

        if (Http\Request::getQueryParam($request, 'address') === null ||
            !Request::areStartAndEndDateValid($startDate, $endDate)) {
            throw new HttpBadRequestException($request, 'Please specify an address, start date and end date and ensure that the end date is after the start date.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendarByRange(DateTime::createFromFormat('j-n-Y', $startDate), DateTime::createFromFormat('j-n-Y', $endDate), 'calendarByRange', 7200, $enableMasking);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Please specify an address, start date and end date and ensure that the end date is after the start date.',
            400,
        );
    }

    #[OA\Get(
        path: '/hijriCalendarByCity/{year}',
        description: 'Returns Prayer times a Hijri year for a city and country',
        summary: 'Prayer times for a Hijri year for a city and country',
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Hijri year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/hijriCalendarByCity/{year}/{month}',
        description: 'Returns Prayer times a Hijri month for a city and country',
        summary: 'Prayer times for a Hijri month for a city and country',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Hijri month for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendarByCity/{year}',
        description: 'Returns Prayer times a Gregorian year for a city and country',
        summary: 'Prayer times for a Gregorian year for a city and country',
        tags: ['Annual Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Gregorian year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarYearResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
        ]
    )]
    #[OA\Get(
        path: '/calendarByCity/{year}/{month}',
        description: 'Returns Prayer times a Gregorian month for a city and country',
        summary: 'Prayer times for a Gregorian month for a city and country',
        tags: ['Monthly Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimesGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer times a Gregorian year for a city and country',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesCityCountryMonthResponse', response: '400')
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
            $r = $ptm->respondWithCalendar((int) $month, (int) $year, $annual, 'calendarByCity', $hijri, 7200, $enableMasking);

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
        path: '/calendarByCity/from/{start}/to/{end}',
        description: 'Returns a prayer times calendar for a city as for a given date range between start date and end date with a maximum date range difference of 11 months',
        summary: 'Prayer times calendar for a city for between 2 dates',
        tags: ['Date Range Prayer Times Calendar'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/start'),
            new OA\PathParameter(ref: '#/components/parameters/end'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimesStateQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesCalMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesShafaqParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTuneParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesSchoolParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesMidNightModeParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesTimeZoneStringParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesLatitudeAdjustmentMethodParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/PrayerTimesIso8601Parameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for a city as per the given date range between start date and end date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesPrayerTimesCalendarMonthResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a city, country, start date and end date and ensure that the end date is after the start date.')
                        ], type: 'object'
                    )
                )
            )
        ]
    )]
    public function calendarByCityByRange(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;
        $startDate = Http\Request::getAttribute($request, 'start');
        $endDate = Http\Request::getAttribute($request, 'end');

        if (Http\Request::getQueryParam($request, 'city') === null ||
            Http\Request::getQueryParam($request, 'country') === null ||
            !Request::areStartAndEndDateValid($startDate, $endDate)) {
            throw new HttpBadRequestException($request, 'Please specify a city, country, start date and end date and ensure that the end date is after the start date.');
        }

        $ptm = new PrayerTimesModel($this->container, $request, $this->mc);

        if (Request::isCalendarRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respondWithCalendarByRange(DateTime::createFromFormat('j-n-Y', $startDate), DateTime::createFromFormat('j-n-Y', $endDate), 'calendarByRange', 7200, $enableMasking);

            return Http\Response::json($response,
                $r,
                200,
                true,
                3600,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Please specify a city, country, start date and end date and ensure that the end date is after the start date.',
            400,
        );
    }


}