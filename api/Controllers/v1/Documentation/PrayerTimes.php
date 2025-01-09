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
        new OA\Tag(name: 'Daily Prayer Times'),
        new OA\Tag(name: 'Monthly Prayer Times Calendar'),
        new OA\Tag(name: 'Annual Prayer Times Calendar'),
        new OA\Tag(name: 'Prayer Time Methods')
    ]
)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: '200CalendarTimingsResponse',
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
                                new OA\Property(property: 'Sunset', type: 'string', example: '16:03 (UTC)'),
                                new OA\Property(property: 'Maghrib', type: 'string', example: '16:03 (UTC)'),
                                new OA\Property(property: 'Isha', type: 'string', example: '17:59 (UTC)'),
                                new OA\Property(property: 'Imsak', type: 'string', example: '05:53 (UTC)'),
                                new OA\Property(property: 'Midnight', type: 'string', example: '00:04 (UTC)'),
                                new OA\Property(property: 'Firstthird', type: 'string', example: '21:24 (UTC)'),
                                new OA\Property(property: 'Lastthird', type: 'string', example: '02:45 (UTC)')
                            ],
                            type: 'object'
                        ),
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
                    properties: [
                        new OA\Property(
                            property: 'timings',
                            properties: [
                                new OA\Property(property: 'Fajr', type: 'string', example: '06:03'),
                                new OA\Property(property: 'Sunrise', type: 'string', example: '08:06'),
                                new OA\Property(property: 'Dhuhr', type: 'string', example: '12:04'),
                                new OA\Property(property: 'Asr', type: 'string', example: '13:44'),
                                new OA\Property(property: 'Sunset', type: 'string', example: '16:03'),
                                new OA\Property(property: 'Maghrib', type: 'string', example: '16:03'),
                                new OA\Property(property: 'Isha', type: 'string', example: '17:59'),
                                new OA\Property(property: 'Imsak', type: 'string', example: '05:53'),
                                new OA\Property(property: 'Midnight', type: 'string', example: '00:04'),
                                new OA\Property(property: 'Firstthird', type: 'string', example: '21:24'),
                                new OA\Property(property: 'Lastthird', type: 'string', example: '02:45')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'date',
                            properties: [
                                new OA\Property(property: 'readable', type: 'string', example: '01 Jan 2025'),
                                new OA\Property(property: 'timestamp', type: 'string', example: '1735714800'),
                                new OA\Property(property: 'hijri',
                                    properties: [
                                        new OA\Property(property: 'date', type: 'string', example: '01-07-1446'),
                                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                        new OA\Property(property: 'day', type: 'string', example: '1'),
                                        new OA\Property(property: 'weekday', properties: [
                                            new OA\Property(property: 'en', type: 'string', example: "Al Arba'a"),
                                            new OA\Property(property: 'ar', type: 'string', example: "الاربعاء"),
                                        ],type: 'object'),
                                        new OA\Property(property: 'month', properties: [
                                            new OA\Property(property: 'number', type: 'integer', example: 7),
                                            new OA\Property(property: 'en', type: 'string', example: "Rajab"),
                                            new OA\Property(property: 'ar', type: 'string', example: "رَجَب"),
                                            new OA\Property(property: 'days', type: 'integer', example: 30)
                                        ], type: 'object'),
                                        new OA\Property(property: 'year', type: 'string', example: '1446'),
                                        new OA\Property(property: 'designation', properties: [
                                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AH'),
                                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Hegirae'),
                                        ],type: 'object'),
                                        new OA\Property(property: 'holidays', type: 'array', items: new OA\Items(), example: ["Beginning of the holy months"]),
                                        new OA\Property(property: 'adjustedHolidays', type: 'array', items: new OA\Items(type: 'string', example: []), example: []),
                                        new OA\Property(property: 'method', type: 'string', example: 'HJCoSA')
                                    ], type: 'object'),

                                new OA\Property(property: 'gregorian',
                                    properties: [
                                        new OA\Property(property: 'date', type: 'string', example: '01-01-2025'),
                                        new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                        new OA\Property(property: 'day', type: 'string', example: '01'),
                                        new OA\Property(property: 'weekday', properties: [
                                            new OA\Property(property: 'en', type: 'string', example: 'Wednesday')
                                        ],type: 'object'),
                                        new OA\Property(property: 'month', properties: [
                                            new OA\Property(property: 'number', type: 'integer', example: 1),
                                            new OA\Property(property: 'en', type: 'string', example: "January"),
                                        ], type: 'object'),
                                        new OA\Property(property: 'year', type: 'string', example: '2025'),
                                        new OA\Property(property: 'designation', properties: [
                                            new OA\Property(property: 'abbreviated', type: 'string', example: 'AD'),
                                            new OA\Property(property: 'expanded', type: 'string', example: 'Anno Domini'),
                                        ],type: 'object'),
                                        new OA\Property(property: 'lunarSighting', type: 'boolean', example: false),
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
                                ref: '#/components/schemas/200CalendarTimingsResponse/properties/data/properties/timings',
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
                                ref: '#/components/schemas/200CalendarTimingsResponse/properties/data/properties/timings',
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
                                    example: '12:04')
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
        ),
        new OA\Schema(
            schema: '200PrayerCalMethodsResponse',
            properties: [
                new OA\Property(property: 'code', ref: '#/components/schemas/200TimingsResponse/properties/code'),
                new OA\Property(property: 'status', ref: '#/components/schemas/200TimingsResponse/properties/status'),
                new OA\Property(property: 'data',
                    properties: [
                        new OA\Property(property: 'MWL',
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
                        new OA\Property(property: 'ISNA',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Islamic Society of North America (ISNA)'),
                                new OA\Property(property: 'params',
                                    properties: [
                                        new OA\Property(property: 'Fajr', type: 'integer', example: 15),
                                        new OA\Property(property: 'Isha', type: 'integer', example: 15),
                                    ], type: 'object'),
                                new OA\Property(property: 'location',
                                    properties: [
                                        new OA\Property(property: 'latitude', type: 'number', example: 39.70421229999999),
                                        new OA\Property(property: 'longitude', type: 'number', example: -86.39943869999999),
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object'),
                        new OA\Property(property: 'EGYPT',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'name', type: 'string', example: 'Egyptian General Authority of Survey'),
                                new OA\Property(property: 'params',
                                    properties: [
                                        new OA\Property(property: 'Fajr', type: 'integer', example: 19.5),
                                        new OA\Property(property: 'Isha', type: 'integer', example: 17.5),
                                    ], type: 'object'),
                                new OA\Property(property: 'location',
                                    properties: [
                                        new OA\Property(property: 'latitude', type: 'number', example: 30.0444196),
                                        new OA\Property(property: 'longitude', type: 'number', example: 31.2357116),
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object'),
                        new OA\Property(property: 'MAKKAH',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 4),
                                new OA\Property(property: 'name', type: 'string', example: 'Umm Al-Qura University, Makkah'),
                                new OA\Property(property: 'params',
                                    properties: [
                                        new OA\Property(property: 'Fajr', type: 'integer', example: 18.5),
                                        new OA\Property(property: 'Isha', type: 'string', example: '90 min'),
                                    ], type: 'object'),
                                new OA\Property(property: 'location',
                                    properties: [
                                        new OA\Property(property: 'latitude', type: 'number', example: 21.3890824),
                                        new OA\Property(property: 'longitude', type: 'number', example: 39.8579118),
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object')
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
            required: true, schema: new OA\Schema(type: 'integer'), example: 7),
        new OA\PathParameter(parameter: 'HijriYear', name: 'year', description: 'Year as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1446),
        new OA\PathParameter(parameter: 'GregorianDate', name: 'date', description: 'Specific gregorian date in DD-MM-YYYY format', in: 'path',
            required: true, schema: new OA\Schema(type: 'string'), example: '01-01-2025'),
        new OA\PathParameter(parameter: 'TimingsGregorianMonth', name: 'month', description: 'Month number as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        new OA\PathParameter(parameter: 'GregorianYear', name: 'year', description: 'Year as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 2025),
        new OA\QueryParameter(parameter: 'LatitudeQueryParameter', name: 'latitude', description: "Latitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '51.5194682'),
        new OA\QueryParameter(parameter: 'LongitudeQueryParameter', name: 'longitude', description: "Longitude coordinates of users location",
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: '-0.1360365'),
        new OA\QueryParameter(parameter: 'TimingsAddressQueryParameter', name: 'address', description: 'Address of user location',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'A5204, London'),
        new OA\QueryParameter(parameter: 'TimingsCityQueryParameter', name: 'city', description: 'Name of the city',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'London'),
        new OA\QueryParameter(parameter: 'TimingsStateQueryParameter', name: 'state', description: 'Name of the state',
            in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'London'),
        new OA\QueryParameter(parameter: 'TimingsCountryQueryParameter', name: 'country', description: 'A country name or 2 character alpha ISO 3166 code',
            in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'GB'),
        new OA\QueryParameter(parameter: '7xAPIKeyQueryParameter', name: 'x7xapikey', description: '7x API Key - An API key from <a href="https://7x.ax" target="_blank">https://7x.ax</a> to geocode the address, city and country. 
        If you do not provide one the response will mask the geocoded co-ordinates.
        ', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'P244d623e2fe2daf56359fGyK3s'),
        new OA\QueryParameter(parameter: 'CalendarMethod', name: 'calendarMethod', description: 'A Calendar Calculation Method. 
        <br />Defaults to HJCoSA.
        <br />- <b>HJCoSA</b> - High Judicial Council of Saudi Arabia (this is used on aladhan.com) 
        <br />- <b>UAQ</b> - Umm al-Qura
        <br />- <b>DIYANET</b> - Diyanet İşleri Başkanlığı
        <br />- <b>MATHEMATICAL</b>
        <br /><br />
        For more details on the methods, please see <a href="https://api.aladhan.com/v1/islamicCalendar/methods" target="_blank">https://api.aladhan.com/v1/islamicCalendar/methods</a>.
        ', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'HJCoSA'), example: 'UAQ'),
        new OA\QueryParameter(parameter: 'Adjustment', name: 'adjustment', description: 'Only applicable if the calendar Method is set to MATHEMATICAL. Number of days to adjust the date being converted to. Example: 1 or 2 or -1 or -2', in: 'path',
            required: false, schema: new OA\Schema(type: 'integer'), example: 0),
        new OA\QueryParameter(parameter: 'PrayerTimingsCalMethodParameter', name: 'method', description: 'A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. 
        If not specified, it defaults to the closest authority based on the location or co-ordinates specified in the API call. This parameter accepts values from 0-23 and 99, as specified below:
        <br /><br />
        Possible values: 
        <ul>
        <li />0 - Jafari / Shia Ithna-Ashari
        <li />1 - University of Islamic Sciences, Karachi
        <li />2 - Islamic Society of North America
        <li />3 - Muslim World League
        <li />4 - Umm Al-Qura University, Makkah
        <li />5 - Egyptian General Authority of Survey
        <li />7 - Institute of Geophysics, University of Tehran
        <li />8 - Gulf Region
        <li />9 - Kuwait
        <li />10 - Qatar
        <li />12 - Majlis Ugama Islam Singapura, Singapore
        <li />12 - Union Organization islamic de France
        <li />13 - Diyanet İşleri Başkanlığı, Turkey
        <li />14 - Spiritual Administration of Muslims of Russia
        <li />15 - Moonsighting Committee Worldwide (also requires shafaq parameter)
        <li />16 - Dubai (experimental)
        <li />17 - Jabatan Kemajuan Islam Malaysia (JAKIM)
        <li />18 - Tunisia
        <li />19 - Algeria
        <li />20 - KEMENAG - Kementerian Agama Republik Indonesia
        <li />21 - Morocco
        <li />22 - Comunidade Islamica de Lisboa
        <li />23 - Ministry of Awqaf, Islamic Affairs and Holy Places, Jordan
        <li />99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
        </ul>
        ', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 99]), example: 3),
        new OA\QueryParameter(parameter: 'PrayerTimingsShafaqParameter', name: 'shafaq', description: "Which Shafaq to use if the 'method' query parameter is 'Moonsighting Commitee Worldwide'. Acceptable options are 'general', 'ahmer' and 'abyad'",
            in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'general', enum: ["general", "ahmer", "abyad"]), example: "general"),
        new OA\QueryParameter(parameter: 'PrayerTimingsTuneParameter', name: 'tune', description: "Comma Separated String of integers to offset timings returned by the API in minutes. The order is Imsak,Fajr,Sunrise,Dhuhr,Asr,Maghrib,Sunset,Isha,Midnight. 
        See <a href='https://aladhan.com/calculation-methods' target='_blank'>https://aladhan.com/calculation-methods</a> for more details.",
            in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: "5,3,5,7,9,-1,0,8,-6"),
        new OA\QueryParameter(parameter: 'PrayerTimingsSchoolParameter', name: 'school', description: "Shafi (or the standard way) or Hanafi.
        <br /><br />
        Possible values:
        <ul>
        <li />0 - Shafi
        <li />1 - Hanafi
        </ul>
        ", in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0, enum: [0, 1]), example: 0),
        new OA\QueryParameter(parameter: 'PrayerTimingsMidNightModeParameter', name: 'midnightMode', description: "Determines the method for calculating midnight
        <br /><br />
        Possible values:
        <ul>
        <li />0 - Standard (Mid Sunset to Sunrise)        
        <li />1 - Jafari (Mid Sunset to Fajr)
        </ul>
        ", in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0, enum: [0, 1]), example: 0),
        new OA\QueryParameter(parameter: 'PrayerTimingsTimeZoneStringParameter', name: 'timezonestring', description: "A valid timezone name as specified on 
        <a href='https://php.net/manual/en/timezones.php' target='_blank'>https://php.net/manual/en/timezones.php</a>. 
        <br /><i>If you do not specify this, we'll calculate it using the co-ordinates you provide.</i>
        ", in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'UTC'),
        new OA\QueryParameter(parameter: 'PrayerTimingsLatitudeAdjustmentMethodParameter', name: 'latitudeAdjustmentMethod', description: "Method for adjusting times at higher latitudes. 
        For example, if you are checking timings in the UK or Sweden.
        <br /><br />
        Possible values:
        <ul>
        <li />1 - Middle of the Night
        <li />2 - One Seventh
        <li />3 - Angle Based
        </ul>
        ", in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [1,2,3])),
        new OA\QueryParameter(parameter: 'PrayerTimingsIso8601Parameter', name: 'iso8601', description: "Whether to return the prayer times in the iso8601 format. 
        Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
        ", in: 'query', required: false, schema: new OA\Schema(type: 'boolean', default: false)),
        new OA\QueryParameter(parameter: 'PrayerTimingsAnnualParameter', name: 'annual', description: "If true, month parameter will be ignored and the calendar for the entire year will be returned",
            in: 'query', required: false, schema: new OA\Schema(type: 'boolean', default: false), example: false),
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
                $this->dir . '/Controllers/v1/PrayerTimesCalendar.php',
                $this->dir . '/Controllers/v1/Methods.php'
            ]
        );

        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'text/yaml']);
    }

}