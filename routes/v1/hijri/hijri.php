<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/islamicCalendar/methods', [Controllers\v1\Hijri::class, 'getMethods']);
    $group->map(['GET', 'OPTIONS'],'/gToHCalendar/{month}/{year}', [Controllers\v1\Hijri::class, 'gregorianToHijriCalendar']);
    $group->map(['GET', 'OPTIONS'],'/hToGCalendar/{month}/{year}', [Controllers\v1\Hijri::class, 'hijriToGregorianCalendar']);
    $group->map(['GET', 'OPTIONS'],'/gToH', [Controllers\v1\Hijri::class, 'gregorianToHijriDate']);
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