<?php
header('Access-Control-Allow-Origin: *');

/** PHP Error handling **/
error_reporting(E_ALL);
ini_set('display_errors', 1);

/** PHP Error handling Ends **/

/** Autoloader **/
require realpath(__DIR__) . '/../vendor/autoload.php';
/** Setup Ends **/

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlAdhanApi\PrayerTimes;
use AlAdhanApi\HijriCalendarService;
use AlAdhanApi\AsmaAlHusna;
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\Database;
use AlAdhanApi\Helper\PrayerTimesHelper;

/** App settings **/
$config['displayErrorDetails'] = true;

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logId = uniqid();
    $logStamp = time();
    $logFile = date('Y-m-d', $logStamp);
    $logTime = $logId . ' :: ' . date('Y-m-d H:i:s :: ');
    // Create the logger
    $logger = new Logger('ApiService');
    // Now add some handlers
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/' . $logFile . '.log', Logger::INFO));
    $logger->addInfo($logTime . 'Incoming request :: ', Log::format($_SERVER, $_REQUEST));
    return $logger;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $r = [
        'code' => 404,
        'status' => 'Not Found',
        'data' => 'Invalid endpoint or resource.'
        ];
        $resp = json_encode($r);
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->write($resp);
    };
};
/** App Settings end **/

$app->get('/asmaAlHusna', function (Request $request, Response $response) {
    $this->logger;
    $names = AsmaAlHusna::get();

    return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

});

$app->get('/asmaAlHusna/{no}', function (Request $request, Response $response) {
    $this->logger;
    $number = $request->getAttribute('no');
    $number = explode(',', $number);
    $nos = [];
    foreach ($number as $no) {
        $nos[] = (int) $no;
    }
    $names = AsmaAlHusna::get($nos);

    if ($names == false) {
        return $response->withJson(ApiResponse::build('Please specify a valid number between 1 and 99', 400, 'Bad Request'), 400);
    }

    return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

});

$app->get('/nextPrayerByAddress', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = Database::getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('@' . time());
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
    }
});

$app->get('/nextPrayerByAddress/{timestamp}', function (Request $request, Response $response) {
    $this->logger;
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = Database::getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
    }
});

$app->get('/currentTime', function (Request $request, Response $response) {
    $this->logger;
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('H:i'), 200, 'OK'), 200);
    }

});

$app->get('/currentDate', function (Request $request, Response $response) {
    $this->logger;
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('d-m-Y'), 200, 'OK'), 200);
    }

});

$app->get('/currentTimestamp', function (Request $request, Response $response) {
    $this->logger;
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('U'), 200, 'OK'), 200);
    }
});

$app->get('/timings', function (Request $request, Response $response) {;
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $timezone = $request->getQueryParam('timezonestring');
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($timezone));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($latitude, $longitude, $timezone, null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timings/{timestamp}', function (Request $request, Response $response) {;
    $this->logger;
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $timezone = $request->getQueryParam('timezonestring');
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($timezone));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod);
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timingsByAddress', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = Database::getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!.', 400, 'Bad Request'), 400);
    }
});


$app->get('/timingsByAddress/{timestamp}', function (Request $request, Response $response) {
    $this->logger;
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = Database::getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timingsByCity', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = Database::getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timingsByCity/{timestamp}', function (Request $request, Response $response) {
    $this->logger;
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = Database::getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && Generic::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!.', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendar', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $timezone = $request->getQueryParam('timezonestring');
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school);
        if ($annual) {
            $times = Generic::calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt);
        } else {
            $times = Generic::calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendarByAddress', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $address = $request->getQueryParam('address');
    $locInfo = Database::getAddressCoOrdinatesAndZone($address);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($annual) {
            $times = Generic::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        } else {
            $times = Generic::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid address.', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendarByCity', function (Request $request, Response $response) {
    $this->logger;
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = Database::getGoogleCoOrdinatesAndZone($city, $country, $state);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($annual) {
            $times = Generic::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        } else {
            $times = Generic::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country pair..', 400, 'Bad Request'), 400);
    }
});

$app->get('/cityInfo', function (Request $request, Response $response) {
    $this->logger;
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $result = Database::getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country.', 400, 'Bad Request'), 400);
    }
});

$app->get('/addressInfo', function (Request $request, Response $response) {
    $this->logger;
    $address = $request->getQueryParam('address');
    $result = Database::getAddressCoOrdinatesAndZone($address);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find address.', 400, 'Bad Request'), 400);
    }
});


/*** Hijri Calendar ***/

$app->get('/gToHCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->logger;
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');

    if ($m > 12) {
        $m = 12;
    }
    if ($m < 1) {
        $m = 1;
    }
    if ($y < 1000) {
        $y = date('Y');
    }

    $days = cal_days_in_month(CAL_GREGORIAN, $m, $y);

    $calendar = [];
    $combineCal = [];
    for($i=1; $i<=$days; $i++) {
        $curDate = $i . '-' . $m . '-' . $y;
        $calendar = $cs->gToH($curDate);
        if ($calendar['hijri']['month']['number'] != $m) {
            unset($calendar[$i]);
        }
        $combineCal[] = $calendar;
    }
        return $response->withJson(ApiResponse::build($combineCal, 200, 'OK'), 200);
});

$app->get('/hToGCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->logger;
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');

    if ($m > 12) {
        $m = 12;
    }
    if ($m < 1) {
        $m = 1;
    }
    if ($y < 1) {
        $y = 1438;
    }

    $days = 30; // Islamic months have 30 or less days - always.

    $calendar = [];
    $combineCal = [];
    for($i=1; $i<=$days; $i++) {
        $curDate = $i . '-' . $m . '-' . $y;
        $calendar = $cs->hToG($curDate);
        if ($calendar['hijri']['month']['number'] != $m) {
            unset($calendar[$i]);
        }
        $combineCal[] = $calendar;
    }
        return $response->withJson(ApiResponse::build($combineCal, 200, 'OK'), 200);
});


$app->get('/gToH', function (Request $request, Response $response) {
    $this->logger;
    $date = $request->getQueryParam('date') == '' || null ? date('d-m-Y', time()) : $request->getQueryParam('date');
    $hs = new HijriCalendarService();
    $result = $hs->gToH($date);
    if ($result) {
        $json = $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        $json = $response->withJson(ApiResponse::build('Invalid date or unable to convert it', 400, 'Bad Request'), 400);
    }
});

$app->get('/hToG', function (Request $request, Response $response) {
    $this->logger;
    $hs = new HijriCalendarService();
    if ($request->getQueryParam('date') == '' || null) {
        $date = date('d-m-Y', time());
        $fs = $hs->gToH($date);
        $date = $fs['hijri']['date'];
    } else {
        $date = $request->getQueryParam('date');
    }
    $result = $hs->hToG($date);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Invalid date or unable to convert it.', 400, 'Bad Request'), 400);
    }
});

$app->get('/nextHijriHoliday', function (Request $request, Response $response) {
    $this->logger;
    $hs = new HijriCalendarService();
    $result = $hs->nextHijriHoliday();;
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute next holiday.', 400, 'Bad Request'), 400);
    }
});

$app->run();
