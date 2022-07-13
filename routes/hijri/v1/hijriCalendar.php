<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Model\HijriCalendarService;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    /**
     * @api {get} http://api.aladhan.com/v1/gToHCalendar/:month/:year Request a Hijri Calendar for a Gregorian month
     * @apiName GetGToHCalendar
     * @apiDescription Request a Hijri Calendar for a Gregorian month
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {number{1-12}} month A gregorian month. 1 = January and 12 = December.
     * @apiParam {number} year A gregorian year. Example: 2015
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/gToHCalendar/8/2016
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     code: 200,
     *     status: "OK",
     *     data: [
     *     {
     *         gregorian: {
     *             date: "01-12-2578",
     *             format: "DD-MM-YYYY",
     *             day: "01",
     *             weekday: {
     *                 en: "Tuesday"
     *             },
     *             month: {
     *                 number: 12,
     *                 en: "December"
     *             },
     *             year: "2578",
     *             designation: {
     *                 abbreviated: "AD",
     *                 expanded: "Anno Domini"
     *             }
     *         },
     *         hijri: {
     *             date: "01-06-2017",
     *             format: "DD-MM-YYYY",
     *             day: "01",
     *             weekday: {
     *                 en: "Al Thalaata",
     *                 ar: "الثلاثاء"
     *             },
     *             month: {
     *                 number: 6,
     *                 en: "Jumādá al-ākhirah",
     *                 ar: "جُمادى الآخرة"
     *             },
     *             year: "2017",
     *             designation: {
     *                 abbreviated: "AH",
     *                 expanded: "Anno Hegirae"
     *             },
     *             holidays: [ ]
     *         }
     *     },
     *
     *     ...  more days
     *
     *     ]
     * }
     */
    $group->get('/gToHCalendar/{month}/{year}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $cs = new HijriCalendarService();

        $y = (int) $request->getAttribute('year');
        $m = (int) $request->getAttribute('month');
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;

        return ApiResponse::print($response, $cs->getGToHCalendar($m, $y, $adjustment), 200, 'OK');
    });


    /**
     * @api {get} http://api.aladhan.com/v1/hToGCalendar/:month/:year Request a Gregoran Calendar for a Hijri month
     * @apiName GetHToGCalendar
     * @apiDescription Request a Gregoran Calendar for a Hijri month
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {number{1-12}} month A Hijri month. 1 = Muharram and 12 = Dhu al Hijjah.
     * @apiParam {number} year A hijri year. Example: 1437
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hToGCalendar/3/1438
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     code: 200,
     *     status: "OK",
     *     data: [
     *     {
     *         gregorian: {
     *             date: "01-12-2578",
     *             format: "DD-MM-YYYY",
     *             day: "01",
     *             weekday: {
     *                 en: "Tuesday"
     *             },
     *             month: {
     *                 number: 12,
     *                 en: "December"
     *             },
     *             year: "2578",
     *             designation: {
     *                 abbreviated: "AD",
     *                 expanded: "Anno Domini"
     *             }
     *         },
     *         hijri: {
     *             date: "01-06-2017",
     *             format: "DD-MM-YYYY",
     *             day: "01",
     *             weekday: {
     *                 en: "Al Thalaata",
     *                 ar: "الثلاثاء"
     *             },
     *             month: {
     *                 number: 6,
     *                 en: "Jumādá al-ākhirah",
     *                 ar: "جُمادى الآخرة"
     *             },
     *             year: "2017",
     *             designation: {
     *                 abbreviated: "AH",
     *                 expanded: "Anno Hegirae"
     *             },
     *             holidays: [ ]
     *         }
     *     },
     *
     *     ...  more days
     *
     *     ]
     * }
     */
    $group->get('/hToGCalendar/{month}/{year}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $cs = new HijriCalendarService();

        $y = (int) $request->getAttribute('year');
        $m = (int) $request->getAttribute('month');
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;

        return ApiResponse::print($response, $cs->getHtoGCalendar($m, $y, $adjustment), 200, 'OK');
    });

    /**
     * @api {get} http://api.aladhan.com/v1/gToH Convert a Gregorian date to a Hijri date
     * @apiName GetGToH
     * @apiDescription Convert a Gregorian date to a Hijri date
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} date A gregorian date formatted as DD-MM-YYYY
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/gToH?date=07-12-2014
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     {
     *         code: 200,
     *         status: "OK",
     *         data: {
     *             hijri: {
     *                 date: "14-02-1436",
     *                 format: "DD-MM-YYYY",
     *                 day: "14",
     *                 month: {
     *                     number: 2,
     *                     en: "Ṣafar",
     *                     ar: "صَفَر"
     *                 },
     *                 year: "1436",
     *                 designation: {
     *                     abbreviated: "AH",
     *                     expanded: "Anno Hegirae"
     *                 }
     *             },
     *             gregorian: {
     *                 date: "07-12-2014",
     *                 format: "DD-MM-YYYY",
     *                 day: "07",
     *                 month: {
     *                     number: 12,
     *                     en: "December"
     *                 },
     *                 year: "2014",
     *                 designation: {
     *                     abbreviated: "AD",
     *                     expanded: "Anno Domini"
     *                 }
     *             }
     *         }
     *     }
     * }
     */
    $group->get('/gToH', function (Request $request, Response $response) {
        $date = isset($request->getQueryParams()['date']) ? $request->getQueryParams()['date'] : date('d-m-Y', time());
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        $hs = new HijriCalendarService();
        $result = $hs->gToH($date, $adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Invalid date or unable to convert it', 400, 'Bad Request');
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/hToG Convert a Hijri date to a Gregorian date
     * @apiName GetHToG
     * @apiDescription Convert a Hijri date to a Gregorian date
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} date A hijri date formatted as DD-MM-YYYY
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hToG?date=14-02-1436
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     {
     *         code: 200,
     *         status: "OK",
     *         data: {
     *             hijri: {
     *                 date: "14-02-1436",
     *                 format: "DD-MM-YYYY",
     *                 day: "14",
     *                 month: {
     *                     number: 2,
     *                     en: "Ṣafar",
     *                     ar: "صَفَر"
     *                 },
     *                 year: "1436",
     *                 designation: {
     *                     abbreviated: "AH",
     *                     expanded: "Anno Hegirae"
     *                 }
     *             },
     *             gregorian: {
     *                 date: "07-12-2014",
     *                 format: "DD-MM-YYYY",
     *                 day: "07",
     *                 month: {
     *                     number: 12,
     *                     en: "December"
     *                 },
     *                 year: "2014",
     *                 designation: {
     *                     abbreviated: "AD",
     *                     expanded: "Anno Domini"
     *                 }
     *             }
     *         }
     *     }
     * }
     */
    $group->get('/hToG', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        if (!isset($request->getQueryParams()['date'])) {
            $date = date('d-m-Y', time());
            $fs = $hs->gToH($date);
            $date = $fs['hijri']['date'];
        } else {
            $date = $request->getQueryParams()['date'];
        }
        $result = $hs->hToG($date, $adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Invalid date or unable to convert it.', 400, 'Bad Request');
        }
    });

    $group->get('/nextHijriHoliday', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        $result = $hs->nextHijriHoliday(360, $adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to compute next holiday.', 400, 'Bad Request');
        }
    });

    $group->get('/currentIslamicYear', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        $result = $hs->getCurrentIslamicYear($adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to compute year.', 400, 'Bad Request');
        }
    });

    $group->get('/currentIslamicMonth', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        $result = $hs->getCurrentIslamicMonth($adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to compute year.', 400, 'Bad Request');
        }
    });

    $group->get('/islamicYearFromGregorianForRamadan/{year}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $y = (int) $request->getAttribute('year');
        $hs = new HijriCalendarService();
        $result = $hs->getIslamicYearFromGregorianForRamadan($y);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to compute year.', 400, 'Bad Request');
        }
    });

    $group->get('/hijriHolidays/{day}/{month}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $d = (int) $request->getAttribute('day');
        $m = (int) $request->getAttribute('month');
        $hs = new HijriCalendarService();
        $result = $hs->getHijriHolidays($d, $m);
        if ($result !== false) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Please specify a valid day and month. Example, 23 and 11.', 400, 'Bad Request');
        }
    });

    $group->get('/specialDays', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $result = $hs->specialDays();
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Something went wrong. Please try again later. Sorry.', 400, 'Bad Request');
        }
    });

    $group->get('/islamicMonths', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $hs = new HijriCalendarService();
        $result = $hs->getIslamicMonths();
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Something went wrong. Please try again later. Sorry.', 400, 'Bad Request');
        }
    });

    $group->get('/islamicHolidaysByHijriYear/{year}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $y = (int) $request->getAttribute('year');
        $adjustment = isset($request->getQueryParams()['adjustment']) ? (int) $request->getQueryParams()['adjustment'] : 0;
        $hs = new HijriCalendarService();
        $result = $hs->getIslamicHolidaysByHijriYear($y, $adjustment);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Something went wrong. Please try again later. Sorry.', 400, 'Bad Request');
        }
    });
});
