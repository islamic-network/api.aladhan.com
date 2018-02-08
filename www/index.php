<?php
header('Access-Control-Allow-Origin: *');

// Setup app.
require_once realpath(__DIR__) . '/../config/init.php';
require_once realpath(__DIR__) . '/../config/dependencies.php';

// Load routes.
require_once realpath(__DIR__) . '/../routes/asmaAlHusna/archive/asmaAlHusna.php';
require_once realpath(__DIR__) . '/../routes/timings/archive/dateAndTime.php';
require_once realpath(__DIR__) . '/../routes/hijri/archive/hijriCalendar.php';
require_once realpath(__DIR__) . '/../routes/timings/archive/prayerTimes.php';
require_once realpath(__DIR__) . '/../routes/asmaAlHusna/v1/asmaAlHusna.php';
require_once realpath(__DIR__) . '/../routes/timings/v1/dateAndTime.php';
require_once realpath(__DIR__) . '/../routes/hijri/v1/hijriCalendar.php';
require_once realpath(__DIR__) . '/../routes/timings/v1/prayerTimes.php';

$app->run();
