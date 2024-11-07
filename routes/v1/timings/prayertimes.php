<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->map(['GET', 'OPTIONS'],'/timings', [Controllers\v1\PrayerTimes::class, 'timings']);
    $group->map(['GET', 'OPTIONS'],'/timings/{date}', [Controllers\v1\PrayerTimes::class, 'timings']);
    $group->map(['GET', 'OPTIONS'],'/timingsByAddress', [Controllers\v1\PrayerTimes::class, 'timingsByAddress']);
    $group->map(['GET', 'OPTIONS'],'/timingsByAddress/{date}', [Controllers\v1\PrayerTimes::class, 'timingsByAddress']);
    $group->map(['GET', 'OPTIONS'],'/timingsByCity', [Controllers\v1\PrayerTimes::class, 'timingsByCity']);
    $group->map(['GET', 'OPTIONS'],'/timingsByCity/{date}', [Controllers\v1\PrayerTimes::class, 'timingsByCity']);
    $group->map(['GET', 'OPTIONS'],'/nextPrayer', [Controllers\v1\PrayerTimes::class, 'nextPrayer']);
    $group->map(['GET', 'OPTIONS'],'/nextPrayer/{date}', [Controllers\v1\PrayerTimes::class, 'nextPrayer']);
    $group->map(['GET', 'OPTIONS'],'/nextPrayerByAddress', [Controllers\v1\PrayerTimes::class, 'nextPrayerByAddress']);
    $group->map(['GET', 'OPTIONS'],'/nextPrayerByAddress/{date}', [Controllers\v1\PrayerTimes::class, 'nextPrayerByAddress']);
});
