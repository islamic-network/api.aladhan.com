<?php
header('Access-Control-Allow-Origin: *');

// Setup app.
require_once realpath(__DIR__) . '/../config/init.php';
require_once realpath(__DIR__) . '/../config/dependencies.php';

// Load routes.
require_once realpath(__DIR__) . '/../routes/asmaAlHusna/asmaAlHusna.php';
require_once realpath(__DIR__) . '/../routes/timings/dateAndTime.php';
require_once realpath(__DIR__) . '/../routes/hijri/hijriCalendar.php';
require_once realpath(__DIR__) . '/../routes/timings/prayerTimes.php';

$app->run();
