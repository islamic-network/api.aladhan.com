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
        description: 'Returns Prayer timings for an entire requested Hijri year',
        summary: 'Prayer timings for a Hijri year',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri year',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse')
                                    )
                                ], type: 'object'
                            )
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendar/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested Hijri month',
        summary: 'Prayer timings for a Hijri month',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri month',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse',
                                )
                            ),
                        ]
                    ),
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendar/{year}',
        description: 'Returns Prayer timings for an entire requested year',
        summary: 'Prayer timings for a year',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested year',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(
                                            ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                        )
                                    )
                                ],
                                type: 'object'
                            )
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsLatLongResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendar/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested month',
        summary: 'Prayer timings for a month',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested month',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                )
                            ),
                        ]
                    ),
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
        description: 'Returns Prayer timings for an entire requested Hijri year based on the given address',
        summary: 'Prayer timings for a Hijri year based on address',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri year based on the given address',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse')
                                    )
                                ], type: 'object'
                            )
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendarByAddress/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested Hijri month based on the given address',
        summary: 'Prayer timings for a Hijri month based on address',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri month based on the given address',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse',
                                )
                            ),
                        ]
                    ),
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByAddress/{year}',
        description: 'Returns Prayer timings for an entire requested year based on the given address',
        summary: 'Prayer timings for a year based on address',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested year based on the given address',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(
                                            ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                        )
                                    )
                                ],
                                type: 'object'
                            )
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByAddress/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested month based on the given address',
        summary: 'Prayer timings for a month based on address',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsAddressQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested month based on the given address',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                )
                            ),
                        ]
                    ),
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
        description: 'Returns Prayer timings for an entire requested Hijri year based on the given city and country',
        summary: 'Prayer timings for a Hijri year based on city and country',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri year based on the given city and country',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse')
                                    )
                                ], type: 'object'
                            )
                        ]
                    ),
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/hijriCalendarByCity/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested Hijri month based on the given city and country',
        summary: 'Prayer timings for a Hijri month based on city and country',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsHijriMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested Hijri month based on the given city and country',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarHijriResponse',
                                )
                            ),
                        ]
                    ),
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByCity/{year}',
        description: 'Returns Prayer timings for an entire requested year based on the given city and country',
        summary: 'Prayer timings for a year based on city and country',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested year based on the given city and country',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', type: 'array',
                                        items: new OA\Items(
                                            ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                        )
                                    )
                                ],
                                type: 'object'
                            )
                        ]
                    ),
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimingsCityCountryMonthResponse', response: '400')
        ]
    )]

    #[OA\Get(
        path: '/calendarByCity/{year}/{month}',
        description: 'Returns Prayer timings for an entire requested month based on the given city and country',
        summary: 'Prayer timings for a month based on city and country',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\PathParameter(ref: '#/components/parameters/TimingsGregorianMonth'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCityQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/TimingsCountryQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/7xAPIKeyQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns Prayer timings for an entire requested month based on the given city and country',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/200TimingsPrayerTimesCalendarResponse',
                                )
                            ),
                        ]
                    ),
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