<?php

namespace Api\Controllers\v1\Documentation;

use Api\Utils\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi as OApi;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "A RESTful API to get islamic prayer times for a given day, month or year.
    The API has several endpoints to assist developers. All the endpoints return JSON and are available over `http` and `https`.
    Please note that prayer timings might not always match your local mosque or government authority. Their timings are likely tuned or adjusted. Please see `https://aladhan.com/calculation-methods` for more details.",
        title: 'Prayer Times API - AlAdhan'
    ),
    servers: [
        new OA\Server(url: 'https://api.aladhan.com/v1'),
        new OA\Server(url: 'http://api.aladhan.com/v1')
    ],
    tags: [
        new OA\Tag(name: 'Timings'),
        new OA\Tag(name: 'Calendar')
    ]
)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: '200TimingsResponse',
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 200),
                new OA\Property(property: 'status', type: 'string', example: 'OK'),
                new OA\Property(property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'timings',
                            properties: [
                                new OA\Property(property: 'Fajr', type: 'string', example: '06:03 (UTC)'),
                                new OA\Property(property: 'Sunrise', type: 'string', example: '08:06 (UTC)'),
                                new OA\Property(property: 'Dhuhr', type: 'string', example: '12:04 (UTC)'),
                                new OA\Property(property: 'Asr', type: 'string', example: '13:44 (UTC)'),
                                new OA\Property(property: 'Sunset', type: 'string', example: '16:02 (UTC)'),
                                new OA\Property(property: 'Maghrib', type: 'string', example: '16:02 (UTC)'),
                                new OA\Property(property: 'Isha', type: 'string', example: '17:59 (UTC)'),
                                new OA\Property(property: 'Imsak', type: 'string', example: '05:53 (UTC)'),
                                new OA\Property(property: 'Midnight', type: 'string', example: '00:04 (UTC)'),
                                new OA\Property(property: 'Firstthird', type: 'string', example: '21:24 (UTC)'),
                                new OA\Property(property: 'Lastthird', type: 'string', example: '02:45 (UTC)')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'date',
                            properties: [
                                new OA\Property(property: 'readable', type: 'string', example: '18 Aug 2021'),
                                new OA\Property(property: 'timestamp', type: 'string', example: '1629270000'),
                                new OA\Property(property: 'hijri',
                                    properties: [
                                        new OA\Property(property: 'date', type: 'string', example: '10-01-1443'),
                                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                        new OA\Property(property: 'day', type: 'integer', example: 10),
                                        new OA\Property(property: 'weekday', properties: [
                                            new OA\Property(property: 'en', type: 'string', example: "Al Arba'a"),
                                            new OA\Property(property: 'ar', type: 'string', example: "الاربعاء"),
                                        ],type: 'object'),
                                        new OA\Property(property: 'month', properties: [
                                            new OA\Property(property: 'number', type: 'integer', example: 1),
                                            new OA\Property(property: 'en', type: 'string', example: "Muḥarram"),
                                            new OA\Property(property: 'ar', type: 'string', example: "مُحَرَّم"),
                                            new OA\Property(property: 'days', type: 'integer', example: 30)
                                        ], type: 'object'),
                                        new OA\Property(property: 'year', type: 'integer', example: 1443),
                                        new OA\Property(property: 'designation', properties: [
                                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AH'),
                                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Hegirae'),
                                        ],type: 'object'),
                                        new OA\Property(property: 'holidays', type: 'array', items: new OA\Items(), example: ["Ashura"]),
                                        new OA\Property(property: 'method', type: 'string', example: 'HJCoSA')
                                    ], type: 'object'),

                                new OA\Property(property: 'gregorian',
                                    properties: [
                                        new OA\Property(property: 'date', type: 'string', example: '18-08-2021'),
                                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                        new OA\Property(property: 'day', type: 'string', example: '18'),
                                        new OA\Property(property: 'weekday', properties: [
                                            new OA\Property(property: 'en', type: 'string', example: 'Wednesday')
                                        ],type: 'object'),
                                        new OA\Property(property: 'month', properties: [
                                            new OA\Property(property: 'number', type: 'integer', example: 8),
                                            new OA\Property(property: 'en', type: 'string', example: "August"),
                                        ], type: 'object'),
                                        new OA\Property(property: 'year', type: 'string', example: '2021'),
                                        new OA\Property(property: 'designation', properties: [
                                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AD'),
                                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Domini'),
                                        ],type: 'object'),
                                    ], type: 'object'),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'latitude', type: 'number', example: 51.5194682),
                                new OA\Property(property: 'longitude', type: 'number', example: -0.1360365),
                                new OA\Property(property: 'timezone', type: 'string', example: 'UTC'),
                                new OA\Property(property: 'method',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 3),
                                        new OA\Property(property: 'name', type: 'string', example: 'Muslim World League'),
                                        new OA\Property(property: 'params',
                                            properties: [
                                                new OA\Property(property: 'Fajr', type: 'integer', example: 18),
                                                new OA\Property(property: 'Isha', type: 'integer', example: 17),
                                            ], type: 'object'),
                                        new OA\Property(property: 'location',
                                            properties: [
                                                new OA\Property(property: 'latitude', type: 'number', example: 51.5194682),
                                                new OA\Property(property: 'longitude', type: 'number', example: -0.1360365),
                                            ],
                                            type: 'object'
                                        )
                                    ],
                                    type: 'object'),
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
                            ],
                            type: 'object',
                        )
                    ],
                    type: 'object',
                )
            ]
        ),
        new OA\Schema(
            schema: '200TimingsPrayerTimesCalendarYearResponse',
            properties: [
                new OA\Property(property: 'code', ref: '#/components/schemas/200TimingsResponse/properties/code'),
                new OA\Property(property: 'status', ref: '#/components/schemas/200TimingsResponse/properties/status'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: '1',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '2',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '3',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '4',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '5',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '6',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '7',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '8',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '9',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '10',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '11',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                        new OA\Property(
                            property: '12',
                            ref: '#/components/schemas/200TimingPartialMonthResponse/properties/1',
                        ),
                    ],
                    type: 'object',
                )
            ],
        ),
        new OA\Schema(
            schema: '200TimingsPrayerTimesCalendarMonthResponse',
            properties: [
                new OA\Property(property: 'code', ref: '#/components/schemas/200TimingsResponse/properties/code'),
                new OA\Property(property: 'status', ref: '#/components/schemas/200TimingsResponse/properties/status'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'timings',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/timings',
                                type: 'object',
                            ),
                            new OA\Property(
                                property: 'date',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/date',
                                type: 'object',
                            ),
                            new OA\Property(
                                property: 'meta',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/meta',
                                type: 'object',
                            ),
                        ],
                        type: 'object'
                    ),
                ),
            ],
        ),

        new OA\Schema(
            schema: '200TimingPartialMonthResponse',
            properties: [
                new OA\Property(
                    property: '1',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'timings',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/timings',
                                type: 'object',
                            ),
                            new OA\Property(
                                property: 'date',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/date',
                                type: 'object',
                            ),
                            new OA\Property(
                                property: 'meta',
                                ref: '#/components/schemas/200TimingsResponse/properties/data/properties/meta',
                                type: 'object',
                            ),
                        ],
                        type: 'object'
                    ),
                ),
            ]
        ),


        new OA\Schema(
            schema: '200TimingsNextPrayerResponse',
            properties: [
                new OA\Property(property: 'code', ref: '#/components/schemas/200TimingsResponse/properties/code'),
                new OA\Property(property: 'status', ref: '#/components/schemas/200TimingsResponse/properties/status'),
                new OA\Property(property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'timings',
                            properties: [
                                new OA\Property(
                                    property: 'Dhuhr',
                                    type: 'string',
                                    example: '13:04')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'date',
                            ref: '#/components/schemas/200TimingsResponse/properties/data/properties/date',
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/200TimingsResponse/properties/data/properties/meta',
                            type: 'object',
                        ),
                    ],
                    type: 'object'
                )
            ]
        )
    ],
    responses: [
        new OA\Response(response: '400TimingsLatLongResponse', description: 'Unable to process request',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 400),
                        new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                        new OA\Property(property: 'data', type: 'integer', example: 'Please specify a valid latitude and longitude.')
                    ],
                )
            )
        ),
        new OA\Response(response: '400TimingsCityCountryMonthResponse', description: 'Unable to process request',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 400),
                        new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                        new OA\Property(property: 'data', type: 'integer', example: 'Please specify a city, country, month and year.')
                    ],
                )
            )
        )
    ],
    parameters: [
        new OA\PathParameter(parameter: 'TimingsHijriMonth', name: 'month', description: 'Month number as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        new OA\PathParameter(parameter: 'HijriYear', name: 'year', description: 'Year as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1439),
        new OA\PathParameter(parameter: 'GregorianDate', name: 'date', description: 'Specific gregorian date in DD-MM-YYYY format', in: 'path',
            required: true, schema: new OA\Schema(type: 'string'), example: '18-08-2021'),
        new OA\PathParameter(parameter: 'TimingsGregorianMonth', name: 'month', description: 'Month number as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        new OA\PathParameter(parameter: 'GregorianYear', name: 'year', description: 'Year as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 2018),
        new OA\QueryParameter(parameter: 'LatitudeQueryParameter', name: 'latitude', description: "Latitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '51.5194682'),
        new OA\QueryParameter(parameter: 'LongitudeQueryParameter', name: 'longitude', description: "Longitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '-0.1360365'),
        new OA\QueryParameter(parameter: 'TimingsAddressQueryParameter', name: 'address', description: 'Address of user location',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'A5204, London'),
        new OA\QueryParameter(parameter: 'TimingsCityQueryParameter', name: 'city', description: 'Name of the city',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'London'),
        new OA\QueryParameter(parameter: 'TimingsStateQueryParameter', name: 'state', description: 'Name of the state',
            in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Florida'),
        new OA\QueryParameter(parameter: 'TimingsCountryQueryParameter', name: 'country', description: 'A country name or 2 character alpha ISO 3166 code',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'GB'),
        new OA\QueryParameter(parameter: '7xAPIKeyQueryParameter', name: 'x7xapikey', description: '7x API Key',
            in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'P244d623e2fe2daf56359fGyK3s'),
        new OA\QueryParameter(parameter: 'CalendarMethod', name: 'calendarMethod', description: 'A Calendar Calculation Method. 
        <br />Defaults to HJCoSA.
        <br />- <b>HJCoSA</b> - High Judicial Council of Saudi Arabia (this is used on aladhan.com) 
        <br />- <b>UAQ</b> - Umm al-Qura
        <br />- <b>DIYANET</b> - Diyanet İşleri Başkanlığı
        <br />- <b>MATHEMATICAL</b>
        <br /><br />
        For more details on the methods, please see <a href="https://api.aladhan.com/v1/islamicCalendar/methods" target="_blank">https://api.aladhan.com/v1/islamicCalendar/methods</a>.
        ', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'UAQ'),
        new OA\QueryParameter(parameter: 'Adjustment', name: 'adjustment', description: 'Only applicable if the calendarMethod is set to MATHEMATICAL. Number of days to adjust the date being converted to. Example: 1 or 2 or -1 or -2', in: 'path',
            required: false, schema: new OA\Schema(type: 'integer'), example: 1),
    ]
)]
class PrayerTimes extends Documentation
{
    public function generate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $openApi = OApi\Generator::scan(
            [
                $this->dir . '/Controllers/v1/Documentation/PrayerTimes.php',
                $this->dir . '/Controllers/v1/PrayerTimes.php',
                $this->dir . '/Controllers/v1/PrayerTimesCalendar.php'
            ]
        );

        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'text/yaml']);
    }

}