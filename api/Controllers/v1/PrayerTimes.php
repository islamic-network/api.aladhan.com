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
        description: 'Returns all prayer times for a specific date',
        summary: 'Prayer times for a specific date',
        tags: ['Daily Prayer Times'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
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
            new OA\Response(response: '200', description: 'Returns all prayer times for a specific date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
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

    #[OA\Get(
        path: '/timingsByAddress/{date}',
        description: 'Returns all prayer times for an address on a specific date',
        summary: 'Prayer times for an address',
        tags: ['Daily Prayer Times'],
        parameters: [
        new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
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
            new OA\Response(response: '200', description: 'Returns all prayer times for the given address on specified date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a valid address.')
                        ],
                    )
                )
            )
        ]
    )]
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

    #[OA\Get(
        path: '/timingsByCity/{date}',
        description: 'Returns all prayer times for a city on a specific date',
        summary: 'Prayer times for a city on a specific date',
        tags: ['Daily Prayer Times'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
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
            new OA\Response(response: '200', description: 'Returns all prayer times for the given city and country on specified date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a valid city and country.')
                        ],
                    )
                )
            )
        ]
    )]
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

    #[OA\Get(
        path: '/nextPrayer/{date}',
        description: 'Returns next prayer time for a specific date',
        summary: 'Next prayer time for a date',
        tags: ['Daily Prayer Times'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
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
            new OA\Response(response: '200', description: 'Returns next prayer times for a specific date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesNextPrayerResponse')
                )
            ),
            new OA\Response(ref: '#/components/responses/400TimesLatLongResponse', response: '400')
        ]
    )]
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

    #[OA\Get(
        path: '/nextPrayerByAddress/{date}',
        description: 'Returns next prayer times for the given address on the specified date',
        summary: 'Next prayer times for an address on the specific date',
        tags: ['Daily Prayer Times'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianDate'),
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
            new OA\Response(response: '200', description: 'Returns next prayer times for an address on the specified date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/200TimesNextPrayerResponse')
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify an address.')
                        ],
                    )
                )
            )
        ]
    )]
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