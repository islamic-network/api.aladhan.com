<?php
header('Access-Control-Allow-Origin: *');

// Setup app.
require_once realpath(__DIR__) . '/../config/init.php';
require_once realpath(__DIR__) . '/../config/dependencies.php';

// Load routes.
require_once realpath(__DIR__) . '/../routes/asmaAlHusna.php';
require_once realpath(__DIR__) . '/../routes/dateAndTime.php';
require_once realpath(__DIR__) . '/../routes/hijriCalendar.php';
require_once realpath(__DIR__) . '/../routes/prayerTimes.php';

$app->run();
