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
        description: 'Gregorian to Hijri calendar conversion',
        summary: 'Convert gregorian to Hijri',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(name: 'month', description: 'Month number as per the gregorian calendar', in: 'path',
                required: true, schema: new OA\Schema(type: 'integer'), example: 1
            ),
            new OA\PathParameter(ref: '#/components/parameters/GregorianYear')
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
                                        new OA\Property(property: 'hijri',
                                            properties: [
                                                new OA\Property(property: 'date', type: 'string', example: '14-04-1439'),
                                                new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                                new OA\Property(property: 'day', type: 'integer', example: 14),
                                                new OA\Property(property: 'weekday', properties: [
                                                    new OA\Property(property: 'en', type: 'string', example: 'Al Athnayn'),
                                                    new OA\Property(property: 'ar', type: 'string', example: "الاثنين"),
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
                                                new OA\Property(property: 'date', type: 'string', example: '01-01-2018'),
                                                new OA\Property(property: 'format', type: 'string', example: 'DD-MM-YYYY'),
                                                new OA\Property(property: 'day', type: 'integer', example: 01),
                                                new OA\Property(property: 'weekday', properties: [
                                                    new OA\Property(property: 'en', type: 'string', example: 'Monday')
                                                ],type: 'object'),
                                                new OA\Property(property: 'month', properties: [
                                                    new OA\Property(property: 'number', type: 'integer', example: 1),
                                                    new OA\Property(property: 'en', type: 'string', example: "January"),
                                                ], type: 'object'),
                                                new OA\Property(property: 'year', type: 'integer', example: 2018),
                                                new OA\Property(property: 'designation', properties: [
                                                    new OA\Property(property: 'abbreviated', type: 'string', example: 'AD'),
                                                    new OA\Property(property: 'expanded', type: 'string', example: 'Anno Domini'),
                                                ],type: 'object'),
                                            ], type: 'object'),
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
        description: 'Hijri to gregorian calendar conversion',
        summary: 'Convert Hijri to gregorian',
        tags: ['Hijri'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/HijriMonth'),
            new OA\PathParameter(ref: '#/components/parameters/HijriYear'),
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
            $this->h->getHtoGCalendar($m, $y, $cm, $adjustment),
            200,
            true,
            604800,
            ['public']
        );
    }

    #[OA\Get(
        path: '/gToH',
        description: 'Gregorian to Hijri date conversion for current date',
        summary: 'Convert gregorian to Hijri',
        tags: ['Hijri'],
        parameters: [
            new OA\QueryParameter(name: 'date', description: 'Automatically appends the current date to the url',
                required: false, schema: new OA\Schema(type: 'string'), example: '19-12-2017?'
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Returns the conversion of current gregorian date to Hijri date',
                content: new OA\MediaType(mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'code', type: 'integer', example: 200),
                            new OA\Property(property: 'status', type: 'string', example: 'OK'),
                            new OA\Property(property: 'data', ref: '#/components/schemas/HijriCalendarDateResponse', type: 'object')
                        ],
                    )
                )
            )
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
        $result = $this->h->gToH($d, $cm, $adjustment);

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
        $result = $this->h->hToG($d, $cm, $adjustment);

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

    public function currentIslamicYear(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            $this->h->getCurrentIslamicYear(),
            200
        );
    }

    public function currentIslamicMonth(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));

        return Http\Response::json($response,
            $this->h->getCurrentIslamicMonth($cm, $adjustment),
            200
        );
    }

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

    public function hijriHolidays(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $d = Http\Request::getAttribute($request, 'day');
        $m = Http\Request::getAttribute($request, 'month');

        if ($d === null && (int)$m === null && (int)$m > 12 && (int)$m < 1 && (int)$d < 1 && (int)$d > 30) {
            return Http\Response::json($response,
                'Please specify a valid day and month',
                400
            );
        }
        $result = Calendar::getHijriHolidays((int)$d, (int)$m);
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

    public function specialDays(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            Calendar::specialDays(),
            200
        );
    }

    public function islamicMonths(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return Http\Response::json($response,
            Calendar::getIslamicMonths(),
            200
        );
    }

    public function islamicHolidaysByHijriYear(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $y = Http\Request::getAttribute($request, 'year');
        $a = Http\Request::getQueryParam($request, 'adjustment');
        $adjustment = $a === null ? 0 : $a;
        $cm = HijriDate::calendarMethod(Http\Request::getQueryParam($request, 'calendarMethod'));
        $result = $this->h->getIslamicHolidaysByHijriYear($cm, $y, $adjustment);
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