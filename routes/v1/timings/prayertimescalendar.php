<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/calendar', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/calendar/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/calendar/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendar']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress/{year}/{month}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByAddress/{year}', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByAddress']);
    $group->map(['GET', 'OPTIONS'],'/calendarByCity', [Controllers\v1\PrayerTimesCalendar::class, 'calendarByCity']);
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
