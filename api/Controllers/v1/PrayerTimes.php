<?php

namespace Api\Controllers\v1;

use Api\Models\HijriCalendar;
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

class PrayerTimes extends Slim
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
        path: '/timings/{date}',
        description: 'Returns all prayer times for a specific date.',
        summary: 'Prayer times for a date',
        tags: ['Timings'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
            new OA\QueryParameter(ref: '#/components/parameters/LatitudeQueryParameter'),
            new OA\QueryParameter(ref: '#/components/parameters/LongitudeQueryParameter')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns all prayer times for a specific date.',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: 'timings',
                                        properties: [
                                            new OA\Property(property: 'Fajr', type: 'string', example: '02:32'),
                                            new OA\Property(property: 'Sunrise', type: 'string', example: '04:51'),
                                            new OA\Property(property: 'Dhuhr', type: 'string', example: '12:04'),
                                            new OA\Property(property: 'Asr', type: 'string', example: '16:01'),
                                            new OA\Property(property: 'Sunset', type: 'string', example: '19:17'),
                                            new OA\Property(property: 'Maghrib', type: 'string', example: '19:17'),
                                            new OA\Property(property: 'Isha', type: 'string', example: '21:25'),
                                            new OA\Property(property: 'Imsak', type: 'string', example: '02:22'),
                                            new OA\Property(property: 'Midnight', type: 'string', example: '00:04'),
                                            new OA\Property(property: 'Firstthird', type: 'string', example: '22:28'),
                                            new OA\Property(property: 'Lastthird', type: 'string', example: '01:40')
                                        ],type: 'object'
                                    ),
                                    new OA\Property(property: 'date',
                                        type: 'object',
                                        allOf: [
                                            new OA\Schema(
                                                properties: [
                                                    new OA\Property(property: 'readable', type: 'string', example: '18 Aug 2021'),
                                                    new OA\Property(property: 'timestamp', type: 'string', example: '1629270000'),
                                                ]
                                            ),
                                            new OA\Schema(ref: '#/components/schemas/HijriHolidayResponse')
                                        ]
                                    ),
                                    new OA\Property(property: 'meta',
                                        properties: [
                                            new OA\Property(property: 'latitude', type: 'number', example: 51.5194682),
                                            new OA\Property(property: 'longitude', type: 'number', example: -0.1360365),
                                            new OA\Property(property: 'timezone', type: 'string', example: 'UTC'),
                                            new OA\Property(property: 'method', ref: '#/components/schemas/PrayerCalMethodsResponse', type: 'object'),
                                            new OA\Property(property: 'latitudeAdjustmentMethod', type: 'string', example: 'ANGLE_BASED'),
                                            new OA\Property(property: 'midnightMode', type: 'string', example: 'STANDARD'),
                                            new OA\Property(property: 'school', type: 'string', example: 'STANDARD'),
                                            new OA\Property(property: 'offset',
                                                properties: [
                                                    new OA\Property(property: 'Imsak', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Fajr', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Sunrise', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Dhuhr', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Asr', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Sunset', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Maghrib', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Isha', type: 'integer', example: 0),
                                                    new OA\Property(property: 'Midnight', type: 'integer', example: 0)
                                                ], type: 'object'
                                            )
                                        ]
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
                3600,
                ['public']
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
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/timingsByAddress/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {

            $r = $ptm->respond($datestring, 'timingsByAddress');

            return Http\Response::json($response,
                ['timings' => $r[0], 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2], $enableMasking)],
                200,
                true,
                3600,
                ['public']
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
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/timingsByCity/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'timingsByCity');

            return Http\Response::json($response,
                ['timings' => $r[0], 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2], $enableMasking)],
                200,
                true,
                3600,
                ['public']
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
            $r = $ptm->respond($datestring, 'nextPrayer', 3600);

            $nextPrayer = PrayerTimesHelper::nextPrayerTime($r[2], $r[3], $ptm->latitude, $ptm->longitude, $ptm->latitudeAdjustmentMethod, $ptm->iso8601, $ptm->timezone);

            return Http\Response::json($response,
                ['timings' => $nextPrayer, 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2])],
                200,
                true,
                300,
                ['public']
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
        $enableMasking = Http\Request::getQueryParam($request, 'x7xapikey') === null;

        if (ApiRequest::redirectableByDate($datestring)) {
            $d = ApiRequest::getRedirectableDate($datestring);
            $d->setTimezone(new DateTimeZone($ptm->timezone));

            return Http\Response::redirect($response, '/v1/nextPrayerByAddress/' . $d->format('d-m-Y') . '?' . http_build_query($request->getQueryParams()), 302);
        }

        if (ApiRequest::isTimingsRequestValid($ptm->latitude, $ptm->longitude, $ptm->timezone)) {
            $r = $ptm->respond($datestring, 'nextPrayerByAddress', 3600);

            $nextPrayer = PrayerTimesHelper::nextPrayerTime($r[2], $r[3], $ptm->latitude, $ptm->longitude, $ptm->latitudeAdjustmentMethod, $ptm->iso8601, $ptm->timezone);

            return Http\Response::json($response,
                ['timings' => $nextPrayer, 'date' => $r[1], 'meta' => PrayerTimesHelper::getMetaArray($r[2], $enableMasking)],
                200,
                true,
                300,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Please specify an address.',
            400,
        );

    }

}