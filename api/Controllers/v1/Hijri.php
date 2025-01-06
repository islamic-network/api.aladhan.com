<?php

namespace Api\Controllers\v1;

use Api\Models\HijriCalendar;
use IslamicNetwork\Calendar\Helpers\Calendar;
use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\HijriDate;
use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.1.0',
    info: new OA\Info(
        version: 'v1',
        description: "An Islamic Calendar API that supports various calculation methods to convert Gregorian Dates to Hijri Dates",
        title: 'Islamic / Hijri Calendar API - AlAdhan'
    ),
    servers: [
        new OA\Server(url: 'https://api.aladhan.com/v1'),
        new OA\Server(url: 'http://api.aladhan.com/v1')
    ],
    tags: [
        new OA\Tag(name: 'Hijri')
    ]

)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'HijriCalendarDateResponse',
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
        )
    ],
    parameters: [
        new OA\PathParameter(parameter: 'HijriMonth', name: 'month', description: 'A Hijri Month - Example: 1 for Muharram', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 4),
        new OA\PathParameter(parameter: 'GregorianMonth', name: 'month', description: 'A Gregorian month - Example: 1 for January', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 4),
        new OA\PathParameter(parameter: 'HijriYear', name: 'year', description: 'Year as per the Hijri calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1439),
        new OA\PathParameter(parameter: 'GregorianYear', name: 'year', description: 'Year as per the Gregorian calendar', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 2018),
        new OA\QueryParameter(parameter: 'CalendarMethod', name: 'calendarMethod', description: 'A Calendar Calculation Method. 
        <br />Defaults to HJCoSA.
        <br />- HJCoSA - High Judicial Council of Saudi Arabia (this is used on aladhan.com) 
        <br />- UAQ - Umm al-Qura
        <br />- DIYANET - Diyanet İşleri Başkanlığı
        <br />- MATHEMATICAL
        <br /><br />
        For more details on the methods, please see https://api.aladhan.com/v1/islamicCalendar/methods.
        ', in: 'path',
            required: false, schema: new OA\Schema(type: 'string'), example: 'UAQ'),
        new OA\QueryParameter(parameter: 'Adjustment', name: 'adjustment', description: 'Only applicable if the calendarMethod is set to MATHEMATICAL. Number of days to adjust the date being converted to. Example: 1 or 2 or -1 or -2', in: 'path',
            required: true, schema: new OA\Schema(type: 'integer'), example: 1),
    ]
)]
class Hijri extends Slim
{
    public HijriCalendar $h;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->h = new HijriCalendar();
    }

    #[OA\Get(
        path: '/gToHCalendar/{month}/{year}',
        description: 'Get a Hijri calendar for a Gregorian month',
        summary: 'Get a Hijri calendar for a Gregorian month',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianMonth'),
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the conversion of gregorian to Hijri calendar',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'object'),
                                    ], type: 'object'

                                )
                            ),
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function gregorianToHijriCalendar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $y = (int)Http\Request::getAttribute($request, 'year');
        $m = (int)Http\Request::getAttribute($request, 'month');
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));

        return Http\Response::json($response,
            $this->h->getGToHCalendar($m, $y, $cm, $adjustment),
            200,
            true,
            604800,
            ['public']
        );
    }

    #[OA\Get(
        path: '/hToGCalendar/{month}/{year}',
        description: 'Get a Gregorian a calendar for a Hijri month',
        summary: 'Get a Gregorian a calendar for a Hijri month',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriMonth'),
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the conversion of Hijri to gregorian calendar',
                content: new OA\MediaType(mediaType: 'application/json',
                        schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/HijriCalendarDateResponse',
                                )
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function hijriToGregorianCalendar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $y = (int)Http\Request::getAttribute($request, 'year');
        $m = (int)Http\Request::getAttribute($request, 'month');
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));

        return Http\Response::json($response,
            $this->h->getHtoGCalendar($m, $y, $cm, (int) $adjustment),
            200,
            true,
            604800,
            ['public']
        );
    }

    #[OA\Get(
        path: '/gToH/{date}',
        description: 'Convert a Gregorian date to a Hijri date',
        summary: 'Convert a Gregorian date to a Hijri date',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(name: 'date', description: 'Gregorian date formatted as  DD-MM-YYYY',
                required: true, schema: new OA\Schema(type: 'string'), example: '19-12-2017'
            ),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the conversion of requested gregorian date to Hijri date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'object')
                        ],
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/404HijriResponse', response: '404')
        ]
    )]
    public function gregorianToHijriDate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $d = Http\Request::getAttribute($request, 'date');
        if ($d === null) {
            $d = Http\Request::getQueryParam($request, 'date');
            if ($d === null) {
                $date = date('d-m-Y', time());

                return Http\Response::redirect($response, '/v1/gToH/' . $date . '?' . http_build_query($request->getQueryParams()), 302);
            }

            return Http\Response::redirect($response, '/v1/gToH/' . $d . '?' . http_build_query($request->getQueryParams()), 301);
        }

        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));
        $result = $this->h->gToH($d, $cm, (int) $adjustment);

        if ($result) {
            return Http\Response::json($response,
                $result,
                200,
                true,
                604800,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Invalid date or unable to convert it.',
            404
        );

    }

    #[OA\Get(
        path: '/hToG/{date}',
        description: 'Convert a Hijri date to a Gregorian date',
        summary: 'Convert a Hijri date to a Gregorian date',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(name: 'date', description: 'Hijri date formatted as DD-MM-YYYY',
                required: true, schema: new OA\Schema(type: 'string'), example: '01-04-1439'
            ),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the conversion of requested Hijri date to gregorian date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'object')
                        ],
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/404HijriResponse', response: '404')
        ]
    )]

    public function hijriToGregorianDate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $d = Http\Request::getAttribute($request, 'date');
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));
        if ($d === null) {
            $d = Http\Request::getQueryParam($request, 'date');
            if ($d === null) {
                $date = date('d-m-Y', time());
                $fs = $this->h->gToH($date, $cm);
                $date = $fs['hijri']['date'];

                return Http\Response::redirect($response, '/v1/hToG/' . $date . '?' . http_build_query($request->getQueryParams()), 302);
            }

            return Http\Response::redirect($response, '/v1/hToG/' . $d . '?' . http_build_query($request->getQueryParams()), 301);
        }

        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $result = $this->h->hToG($d, $cm, (int) $adjustment);

        if ($result) {
            return Http\Response::json($response,
                $result,
                200,
                true,
                604800,
                ['public']
            );
        }

        return Http\Response::json($response,
            'Invalid date or unable to convert it.',
            404
        );
    }

    #[OA\Get(
        path: '/nextHijriHoliday',
        description: 'Get the next upcoming holiday in the Hijri calendar',
        summary: 'Next Hijri holiday',
        tags: ['Hijri'],
        parameters: [
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod'),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the next holiday in the Hijri calendar',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'object')
                        ],
                    )
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                schema:  new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Unable to compute next holiday.')
                        ]
                    )
                )
            )
        ]
    )]
    public function nextHijriHoliday(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));
        $result = $this->h->nextHijriHoliday($cm, 360, $adjustment);

        if ($result) {
            return Http\Response::json($response,
                $result,
                200
            );
        }

        return Http\Response::json($response,
            'Unable to compute next holiday.',
            400
        );
    }

    #[OA\Get(
        path: '/currentIslamicYear',
        description: 'Returns current Islamic year',
        summary: 'Current Islamic year',
        tags: ['Hijri'],
        responses: [
            new OA\Response(ref: '#/components/responses/200HijriCurrentYearResponse', response: '200')
        ]
    )]
    public function currentIslamicYear(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            $this->h->getCurrentIslamicYear(),
            200
        );
    }

    #[OA\Get(
        path: '/currentIslamicMonth',
        description: 'Get the current Islamic month',
        summary: 'Get the current Islamic month',
        tags: ['Hijri'],
        responses: [
            new OA\Response(response: '200', description: 'Returns current Islamic month',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'integer', example: 6)
                        ],
                    )
                )
            )
        ]
    )]
    public function currentIslamicMonth(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));

        return Http\Response::json($response,
            $this->h->getCurrentIslamicMonth($cm, (int) $adjustment),
            200
        );
    }

    #[OA\Get(
        path: '/islamicYearFromGregorianForRamadan/{year}',
        description: 'Returns Islamic year from gregorian year',
        summary: 'Islamic year from gregorian year',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear')
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/200HijriCurrentYearResponse', response: '200', description: 'Returns Islamic year equivalent to the requested gregorian year'),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema:  new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a valid year')
                        ]
                    )
                )
            )
        ]
    )]
    public function islamicYearFromGregorianForRamadan(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $y = Http\Request::getAttribute($request, 'year');

        if ($y !== null && (int)$y > 622) {
            return Http\Response::json($response,
                $this->h->getIslamicYearFromGregorianForRamadan((int)$y),
                200
            );
        }

        return Http\Response::json($response,
            'Please specify a valid year',
            400
        );
    }

    #[OA\Get(
        path: '/hijriHolidays/{day}/{month}',
        description: 'Returns a holiday for a specific day of a month as per Hijri calendar',
        summary: 'Holiday for a specific Hijri day',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(name: 'day', description: 'Day in a Hijri month', in: 'path',
                required: true, schema: new OA\Schema(type: 'integer'), example: 10
            ),
            new OA\PathParameter(ref: '#/components/parameters/HijriMonth')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns which holiday is on the requested Hijri day of the month',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string'), example: ['Ashura'])
                        ]
                    )
                )
            ),
            new OA\Response(response: '400', description: 'Unable to process request',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema:  new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 400),
                            new OA\Property(property: 'status', type: 'string', example: 'BAD_REQUEST'),
                            new OA\Property(property: 'data', type: 'string', example: 'Please specify a valid day and month')
                        ]
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/404HijriHolidaysResponse', response: '404')
        ]
    )]
    public function hijriHolidays(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $d = Http\Request::getAttribute($request, 'day');
        $m = Http\Request::getAttribute($request, 'month');

        if ($d === null && (int) $m === null && (int) $m > 12 && (int) $m < 1 && (int) $d < 1 && (int) $d > 30) {
            return Http\Response::json($response,
                'Please specify a valid day and month',
                400
            );
        }
        $result = Calendar::getHijriHolidays((int) $d, (int) $m);
        if (!empty($result)) {
            return Http\Response::json($response,
                $result,
                200
            );
        }

        return Http\Response::json($response,
            'No holidays found.',
            404
        );
    }
    #[OA\Get(
        path: '/specialDays',
        description: 'Returns a list of special days as per Hijri calendar',
        summary: 'List of special days',
        tags: ['Hijri'],
        responses: [
            new OA\Response(response: '200', description: 'Returns a list of special days as per Hijri calendar',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'month', type: 'integer', example: 1),
                                        new OA\Property(property: 'day', type: 'integer', example: 10),
                                        new OA\Property(property: 'name', type: 'string', example: 'Ashura')
                                    ],
                                    type: 'object'
                                )
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function specialDays(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            Calendar::specialDays(),
            200
        );
    }

    #[OA\Get(
        path: '/islamicMonths',
        description: 'Returns a list of Islamic months as per Hijri calendar',
        summary: 'Islamic months',
        tags: ['Hijri'],
        responses: [
            new OA\Response(response: '200', description: 'Returns a list of Islamic months',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data',
                                properties: [
                                    new OA\Property(property: '1', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 1),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Muḥarram'),
                                        new OA\Property(property: 'ar', type: 'string', example: "مُحَرَّم")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '2', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 2),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Ṣafar'),
                                        new OA\Property(property: 'ar', type: 'string', example: "صَفَر")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '3', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 3),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Rabīʿ al-awwal'),
                                        new OA\Property(property: 'ar', type: 'string', example: "رَبيع الأوّل")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '4', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 4),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Rabīʿ al-thānī'),
                                        new OA\Property(property: 'ar', type: 'string', example: "رَبيع الثاني")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '5', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 5),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Jumādá al-ūlá'),
                                        new OA\Property(property: 'ar', type: 'string', example: "جُمادى الأولى")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '6', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 6),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Jumādá al-ākhirah'),
                                        new OA\Property(property: 'ar', type: 'string', example: "جُمادى الآخرة")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '7', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 7),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Rajab'),
                                        new OA\Property(property: 'ar', type: 'string', example: "رَجَب")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '8', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 8),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Shaʿbān'),
                                        new OA\Property(property: 'ar', type: 'string', example: "شَعْبان")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '9', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 9),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Ramaḍān'),
                                        new OA\Property(property: 'ar', type: 'string', example: "رَمَضان")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '10', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 10),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Shawwāl'),
                                        new OA\Property(property: 'ar', type: 'string', example: "شَوّال")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '11', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 11),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Dhū al-Qaʿdah'),
                                        new OA\Property(property: 'ar', type: 'string', example: "ذوالقعدة")
                                    ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: '12', properties: [
                                        new OA\Property(property: 'number', type: 'integer', example: 12),
                                        new OA\Property(property: 'en', type: 'integer', example: 'Dhū al-Ḥijjah'),
                                        new OA\Property(property: 'ar', type: 'string', example: "ذوالحجة")
                                    ],
                                        type: 'object'
                                    ),
                                ],
                                type: 'object',
                            )
                        ]
                    )
                )
            )
        ]
    )]
    public function islamicMonths(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            Calendar::getIslamicMonths(),
            200
        );
    }

    #[OA\Get(
        path: '/islamicHolidaysByHijriYear/{year}',
        description: 'Returns a list of holidays as per the Hijri year',
        summary: 'Hijri holidays by year',
        tags: ['Hijri'],
        parameters: [
            new OA\Parameter(name: 'year', description: 'A Hijri year', in: 'path',
                required: true, schema: new OA\Schema(type: 'integer'), example: 1443),
            new OA\QueryParameter(ref: '#/components/parameters/Adjustment'),
            new OA\QueryParameter(ref: '#/components/parameters/CalendarMethod')
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns list of holidays as per the requested Hijri year',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'array', items: new OA\Items(type: 'object'))
                        ],
                    )
                )
            ),
            new OA\Response(ref: '#/components/responses/404HijriHolidaysResponse', response: '404')
        ]
    )]
    public function islamicHolidaysByHijriYear(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $y = (int) Http\Request::getAttribute($request, 'year');
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));
        $result = $this->h->getIslamicHolidaysByHijriYear($cm, $y, (int) $adjustment);
        if (!empty($result)) {
            return Http\Response::json($response,
                $result,
                200
            );
        }

        return Http\Response::json($response,
            'No holidays found.',
            404
        );
    }

    #[OA\Get(
        path: '/islamicCalendar/methods',
        description: 'Returns a list of methods that are used for different types of Hijri / Islamic Calendar calculations',
        summary: 'Hirji / Islamic Calendar Calculations',
        tags: ['Hijri'],
        responses: [
            new OA\Response(response: '200', description: 'Returns a list of methods used for Hijri / Islamic Calendar calculations',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', type: 'array',
                                items: new OA\Items(
                                    oneOf: [
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string', example: 'UAQ'),
                                                new OA\Property(property: 'name', type: 'string', example: 'Umm al-Qura'),
                                                new OA\Property(property: 'description', type: 'string', example: 'The Umm Al-Qura Calender based on astronomical data provided by the Umm al-Qura University in Makkah, Saudi Arabia. Strictly speaking, this calendar is intended for civil purposes only. Please keep in mind that the first visual sighting of the lunar crescent (hilāl) can occur up to two days after the date predicted by the Umm al-Qura calendar.'),
                                                new OA\Property(property: 'validity', type: 'string', example: '1356 AH (14 March 1937 CE) to 1500 AH (16 November 2077 CE)')
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string', example: 'HJCoSA'),
                                                new OA\Property(property: 'name', type: 'string', example: 'Majlis al-Qadāʾ al-Aʿlā (High Judiciary Council of Saudi Arabia)'),
                                                new OA\Property(property: 'description', type: 'string', example: 'This calendar is based on the Umm al-Qura calendar, but the dates for the months of Muḥarram, Ramaḍān, Shawwāl and Dhu ʾl-Ḥijja are adjusted after reported sightings of the lunar crescent announced by the Majlis al-Qadāʾ al-Aʿlā (High Judiciary Council of Saudi Arabia). Please also see https://webspace.science.uu.nl/~gent0113/islam/ummalqura_adjust.htm for more details.'),
                                                new OA\Property(property: 'validity', type: 'string', example: '1356 AH (14 March 1937 CE) to 1500 AH (16 November 2077 CE)')
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string', example: 'MATHEMATICAL'),
                                                new OA\Property(property: 'name', type: 'string', example: 'Mathematical calculator based on a calculation written by Layth A. Ibraheem'),
                                                new OA\Property(property: 'description', type: 'string', example: 'This has been the default calendar used by the AlAdhan API until January 2025. It is purely mathematical does not \n    keep track of the number of days in a month, so adjusting it to match the actual hilaal sightings is not possible. It still works if you are happy \n    wth some inconsistencies, but is no longer the default calendar. This calendar allows for adjustments.'),
                                                new OA\Property(property: 'validity', type: 'string', example: 'No restrictions')
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(property: 'id', type: 'string', example: 'DIYANET'),
                                                new OA\Property(property: 'name', type: 'string', example: 'Diyanet İşleri Başkanlığı'),
                                                new OA\Property(property: 'description', type: 'string', example: 'The Islamic Calendar of Tukey based on astronomical data provided by Turkish Presidency of Religious Affairs (Diyanet İşleri Başkanlığı.'),
                                                new OA\Property(property: 'validity', type: 'string', example: '1 Muharram 1318 AH (1 May 1900) to 29 Şaban 1449 AH (26 January 2028)')
                                            ],
                                            type: 'object'
                                        ),
                                    ],
                                )
                            ),
                        ]
                    )
                )
            )
        ]
    )]
    public function getMethods(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            HijriDate::getCalendarMethods(),
            200
        );
    }

}