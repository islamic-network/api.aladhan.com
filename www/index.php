<?php
header('Access-Control-Allow-Origin: *');

require_once realpath(__DIR__) . '/../config/init.php';
require_once realpath(__DIR__) . '/../config/dependencies.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Model\PrayerTimes;
use AlAdhanApi\Model\HijriCalendarService;
use AlAdhanApi\Model\AsmaAlHusna;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\PrayerTimesHelper;

$app->get('/asmaAlHusna', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $names = AsmaAlHusna::get();

    return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

});

$app->get('/asmaAlHusna/{no}', function (Request $request, Response $response) {
    $this->helper->logger->write();
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
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('@' . time());
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('H:i'), 200, 'OK'), 200);
    }

});

$app->get('/currentDate', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('d-m-Y'), 200, 'OK'), 200);
    }

});

$app->get('/currentTimestamp', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('U'), 200, 'OK'), 200);
    }
});

$app->get('/timings', function (Request $request, Response $response) {;
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $timezone = $request->getQueryParam('timezonestring');
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($timezone));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
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
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = $this->helper->database->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = $this->helper->database->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
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
    $this->helper->logger->write();
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
            $times = PrayerTimesHelper::calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt);
        } else {
            $times = PrayerTimesHelper::calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendarByAddress', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $address = $request->getQueryParam('address');
    $locInfo = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($annual) {
            $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        } else {
            $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid address.', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendarByCity', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $locInfo = $this->helper->database->getGoogleCoOrdinatesAndZone($city, $country, $state);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($annual) {
            $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        } else {
            $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country pair..', 400, 'Bad Request'), 400);
    }
});

$app->get('/cityInfo', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $result = $this->helper->database->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country.', 400, 'Bad Request'), 400);
    }
});

$app->get('/addressInfo', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $address = $request->getQueryParam('address');
    $result = $this->helper->database->getAddressCoOrdinatesAndZone($address);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find address.', 400, 'Bad Request'), 400);
    }
});


/*** Hijri Calendar ***/

$app->get('/gToHCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');


    return $response->withJson(ApiResponse::build($cs->getGToHCalendar($m, $y), 200, 'OK'), 200);
});

$app->get('/hToGCalendar/{month}/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $cs = new HijriCalendarService();

    $y = (int) $request->getAttribute('year');
    $m = (int) $request->getAttribute('month');

    return $response->withJson(ApiResponse::build($cs->getHtoGCalendar($m, $y), 200, 'OK'), 200);
});


$app->get('/gToH', function (Request $request, Response $response) {
    $this->helper->logger->write();
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
    $this->helper->logger->write();
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
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->nextHijriHoliday();;
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute next holiday.', 400, 'Bad Request'), 400);
    }
});

$app->get('/currentIslamicYear', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->getCurrentIslamicYear();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->get('/currentIslamicMonth', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $hs = new HijriCalendarService();
    $result = $hs->getCurrentIslamicMonth();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->get('/islamicYearFromGregorianForRamadan/{year}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $y = (int) $request->getAttribute('year');
    $hs = new HijriCalendarService();
    $result = $hs->getIslamicYearFromGregorianForRamadan();
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to compute year.', 400, 'Bad Request'), 400);
    }
});

$app->run();
