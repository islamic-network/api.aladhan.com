<?php

namespace Api\Controllers;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi as OApi;
use Api\Utils\Response;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "A RESTful API to get islamic prayer times for a given day, month or year.
    The API has several endpoints to assist developers. All the endpoints return JSON and are available over `http` and `https`.
    Please note that prayer timings might not always match your local mosque or government authority. Their timings are likely tuned or adjusted. Please see `https://aladhan.com/calculation-methods` for more details.",
        title: 'AlAdhan - Prayer Times API'
    ),
    servers: [
        new OA\Server(url: 'http://api.aladhan.com'),
        new OA\Server(url: 'https://api.aladhan.com')
    ],
    tags: [
        new OA\Tag(name: 'AsmaAlHusna'),
        new OA\Tag(name: 'Hijri'),
        new OA\Tag(name: 'Qibla'),
        new OA\Tag(name: 'Timings'),
        new OA\Tag(name: 'DateAndTime')
    ]
)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'AsmaAlHusnaResponse',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: "الرَّحْمَنُ"),
                new OA\Property(property: 'transliteration', type: 'string', example: 'Ar Rahmaan'),
                new OA\Property(property: 'number', type: 'integer', example: 1),
                new OA\Property(property: 'en', properties: [
                    new OA\Property(property: 'meaning', type: 'string', example: 'The Beneficent')
                ],
                    type: 'object'),
            ],
        ),
        new OA\Schema(
            schema: 'HijriCalendarDateResponse',
            properties: [
                new OA\Property(property: 'hijri',
                    properties: [
                        new OA\Property(property: 'date', type: 'string', example: '01-04-1439'),
                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                        new OA\Property(property: 'day', type: 'integer', example: 1),
                        new OA\Property(property: 'weekday', properties: [
                            new OA\Property(property: 'en', type: 'string', example: 'Al Thalaata'),
                            new OA\Property(property: 'ar', type: 'string', example: "الثلاثاء"),
                        ],type: 'object'),
                        new OA\Property(property: 'month', properties: [
                            new OA\Property(property: 'number', type: 'integer', example: 4),
                            new OA\Property(property: 'en', type: 'string', example: "Rabīʿ al-thānī"),
                            new OA\Property(property: 'ar', type: 'string', example: "رَبيع الثاني"),
                            new OA\Property(property: 'days', type: 'integer', example: 30)
                        ], type: 'object'),
                        new OA\Property(property: 'year', type: 'integer', example: 1439),
                        new OA\Property(property: 'designation', properties: [
                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AH'),
                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Hegirae'),
                        ],type: 'object'),
                        new OA\Property(property: 'holidays', type: 'array', items: new OA\Items(), example: []),
                        new OA\Property(property: 'method', type: 'string', example: 'HJCoSA')
                    ], type: 'object'),

                new OA\Property(property: 'gregorian',
                    properties: [
                        new OA\Property(property: 'date', type: 'string', example: '19-12-2017'),
                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                        new OA\Property(property: 'day', type: 'string', example: '19'),
                        new OA\Property(property: 'weekday', properties: [
                            new OA\Property(property: 'en', type: 'string', example: 'Tuesday')
                        ],type: 'object'),
                        new OA\Property(property: 'month', properties: [
                            new OA\Property(property: 'number', type: 'integer', example: 12),
                            new OA\Property(property: 'en', type: 'string', example: "December"),
                        ], type: 'object'),
                        new OA\Property(property: 'year', type: 'string', example: '2017'),
                        new OA\Property(property: 'designation', properties: [
                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AD'),
                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Domini'),
                        ],type: 'object'),
                    ], type: 'object'),
            ]
        ),
        new OA\Schema(
            schema: 'HijriHolidayResponse',
            properties: [
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
            ]
        ),
        new OA\Schema(
            schema: 'PrayerCalMethodsResponse',
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
            ]
        ),
        new OA\Schema(
            schema: '200TimingPartialResponse',
            properties: [
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
            ]
        ),
        new OA\Schema(
            schema: '200TimingsResponse',
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 200),
                new OA\Property(property: 'status', type: 'string', example: 'OK'),
                new OA\Property(property: 'data',
                    type: 'object', allOf: [
                        new OA\Schema(
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
                                )
                            ]
                        ),
                        new OA\Schema(ref: '#/components/schemas/200TimingPartialResponse')
                    ]
                )
            ]
        ),
        new OA\Schema(
            schema: '200TimingsNextPrayerResponse',
            properties: [
                new OA\Property(property: 'code', type: 'integer', example: 200),
                new OA\Property(property: 'status', type: 'string', example: 'OK'),
                new OA\Property(property: 'data',
                    type: 'object',
                    allOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'timings',
                                    properties: [
                                        new OA\Property(property: 'Dhuhr', type: 'string', example: '13:04')
                                    ],type: 'object'
                                )
                            ]
                        ),
                        new OA\Schema(ref: '#/components/schemas/200TimingPartialResponse')
                    ]
                )
            ]
        )
    ],
    responses: [
        new OA\Response(response: '404HijriResponse', description: 'NOT FOUND - Unable to process request',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 404),
                        new OA\Property(property: 'status', type: 'string', example: 'NOT FOUND'),
                        new OA\Property(property: 'data', type: 'string', example: 'Invalid date or unable to convert it.')
                    ]
                )
            )
        ),
        new OA\Response(response: '200HijriCurrentYearResponse', description: 'Returns current Islamic year',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 200),
                        new OA\Property(property: 'status', type: 'string', example: 'OK'),
                        new OA\Property(property: 'data', type: 'integer', example: 1446)
                    ],
                )
            )
        ),
        new OA\Response(response: '404HijriHolidaysResponse', description: 'NOT FOUND - Unable to process request',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 404),
                        new OA\Property(property: 'status', type: 'string', example: 'NOT FOUND'),
                        new OA\Property(property: 'data', type: 'string', example: 'No holidays found.')
                    ]
                )
            )
        ),
        new OA\Response(response: '400DateTimeResponse', description: 'Unable to process request',
            content: new OA\MediaType(mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 400),
                        new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                        new OA\Property(property: 'data', type: 'integer', example: 'Please specify a valid timezone. Example: Europe/London')
                    ],
                )
            )
        ),
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
        )
    ],
    parameters: [
        new OA\PathParameter(parameter: 'HijriMonth', name: 'month', description: 'Month number as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 4),
        new OA\PathParameter(parameter: 'HijriYear', name: 'year', description: 'Year as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1439),
        new OA\PathParameter(parameter: 'GregorianYear', name: 'year', description: 'Year as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 2018),
        new OA\PathParameter(parameter: 'GregorianDate', name: 'date', description: 'Specific gregorian date in DD-MM-YYYY format', in: 'path',
            required: true, schema: new OA\Schema(type: 'string'), example: '18-08-2021'),
        new OA\PathParameter(parameter: 'AutoAppendGregorianDate', name: 'date', description: 'Automatically appends current gregorian date to the url', in: 'path',
            required: true, schema: new OA\Schema(type: 'string'), example: '18-08-2021'),

        new OA\QueryParameter(parameter: 'TimeZoneQueryParameter', name: 'zone', description: 'TimeZone', in: 'query',
            required: true, schema: new OA\Schema(type: 'string'), example: '?zone=Asia/Gaza'),
        new OA\QueryParameter(parameter: 'LatitudeQueryParameter', name: 'latitude', description: "Latitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '?latitude=51.5194682'),
        new OA\QueryParameter(parameter: 'LongitudeQueryParameter', name: 'longitude', description: "Longitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '&longitude=-0.1360365'),
        new OA\QueryParameter(parameter: '7xAPIKeyQueryParameter', name: 'x7xapikey', description: '7x API Key', in: 'query',
            required: false, schema: new OA\Schema(type: 'string'), example: '&x7xapikey=P244d623e2fe2daf56359fxxxxx')
    ],
)]
class Documentation
{
    public function generate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $urlPattern = $request->getUri()->getPath();
        $controller = explode('/', $urlPattern);

        if ($controller[3] === 'prayer-times')
        {
            $openApi = OApi\Generator::scan(['/var/www/api/Controllers/v1/PrayerTimes']);
        } elseif ($controller[3] === 'islamic-calendar') {
            $openApi = OApi\Generator::scan(['/var/www/api/Controllers/partials/Hijri.php', '/var/www/api/Controllers/v1/Hijri.php']);
        } elseif ($controller[3] === 'qibla') {
            $openApi = OApi\Generator::scan(['/var/www/api/Controllers/partials/Qibla.php', '/var/www/api/Controllers/v1/Qibla.php']);
        } elseif ($controller[3] === 'asma-al-husna') {
            $openApi = OApi\Generator::scan(['/var/www/api/Controllers/partials/AsmaAlHusna.php', '/var/www/api/Controllers/v1/AsmaAlHusna.php']);
        } else {
            $openApi = OApi\Generator::scan(['/var/www/api/Controllers/Documentation.php', '/var/www/api/Controllers/v1']);
        }
//        header('Content-Type: application/x-yaml');

//        return Http\Response::json($response, $openApi->toYaml(), 200);
        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'text/yaml']);
    }
}