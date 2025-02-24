<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var Slim\App $app
 */

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/calendar/from/{start}/to/{end}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByRange']);
    $group->map(['GET', 'OPTIONS'],'/calendar/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/calendar/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress/from/{start}/to/{end}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddressByRange']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByCity', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
    $group->map(['GET', 'OPTIONS'],'/calendarByCity/from/{start}/to/{end}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCityByRange']);
    $group->map(['GET', 'OPTIONS'],'/calendarByCity/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
    $group->map(['GET', 'OPTIONS'],'/calendarByCity/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendar', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendar/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendar/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByAddress', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByAddress/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByAddress/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByCity', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByCity/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
    $group->map(['GET', 'OPTIONS'],'/hijriCalendarByCity/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
});
