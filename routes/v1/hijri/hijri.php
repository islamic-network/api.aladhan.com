<?php

use Api\Controllers;
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
    $group->map(['GET', 'OPTIONS'],'/gToHCalendar/{month}/{year}', [Controllers\v1\Hijri::class, 'greogorianToHijriCalendar']);

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
    $group->map(['GET', 'OPTIONS'],'/hToGCalendar/{month}/{year}', [Controllers\v1\Hijri::class, 'hijriToGregorianCalendar']);

    /**
     * @api {get} http://api.aladhan.com/v1/gToH/:date Convert a Gregorian date to a Hijri date
     * @apiName GetGToH
     * @apiDescription Convert a Gregorian date to a Hijri date
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} date A gregorian date formatted as DD-MM-YYYY
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/gToH/07-12-2014
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
    $group->map(['GET', 'OPTIONS'],'/gToH', [Controllers\v1\Hijri::class, 'gregorianToHijriDate']);

    /**
     * @api {get} http://api.aladhan.com/v1/hToG/:date Convert a Hijri date to a Gregorian date
     * @apiName GetHToG
     * @apiDescription Convert a Hijri date to a Gregorian date
     * @apiGroup IslamicCalendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} date A hijri date formatted as DD-MM-YYYY
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hToG/14-02-1436
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
    $group->map(['GET', 'OPTIONS'],'/hToG', [Controllers\v1\Hijri::class, 'hijriToGregorianDate']);
    $group->map(['GET', 'OPTIONS'],'/gToH/{date}', [Controllers\v1\Hijri::class, 'gregorianToHijriDate']);
    $group->map(['GET', 'OPTIONS'],'/hToG/{date}', [Controllers\v1\Hijri::class, 'hijriToGregorianDate']);
    $group->map(['GET', 'OPTIONS'],'/nextHijriHoliday', [Controllers\v1\Hijri::class, 'nextHijriHoliday']);

    $group->map(['GET', 'OPTIONS'],'/currentIslamicYear', [Controllers\v1\Hijri::class, 'currentIslamicYear']);
    $group->map(['GET', 'OPTIONS'],'/currentIslamicMonth', [Controllers\v1\Hijri::class, 'currentIslamicMonth']);
    $group->map(['GET', 'OPTIONS'],'/islamicYearFromGregorianForRamadan/{year}', [Controllers\v1\Hijri::class, 'islamicYearFromGregorianForRamadan']);
    $group->map(['GET', 'OPTIONS'],'/hijriHolidays/{day}/{month}', [Controllers\v1\Hijri::class, 'hijriHolidays']);
    $group->map(['GET', 'OPTIONS'],'/specialDays', [Controllers\v1\Hijri::class, 'specialDays']);
    $group->map(['GET', 'OPTIONS'],'/islamicMonths', [Controllers\v1\Hijri::class, 'islamicMonths']);
    $group->map(['GET', 'OPTIONS'],'/islamicHolidaysByHijriYear/{year}', [Controllers\v1\Hijri::class, 'islamicHolidaysByHijriYear']);
});