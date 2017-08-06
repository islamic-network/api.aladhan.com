<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Meezaan\PrayerTimes\PrayerTimes;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\PrayerTimesHelper;
use AlAdhanApi\Helper\Generic;

$app->get('/nextPrayerByAddress', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime('@' . time());
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
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
    $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
    }
});


$app->get('/timings', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    $timezone = Generic::computeTimezone($latitude, $longitude, $request->getQueryParam('timezonestring'), $this->model->locations);
    if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime('now', new DateTimeZone($timezone));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($latitude, $longitude, $timezone, null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude and longitude.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timings/{timestamp}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    $timezone = Generic::computeTimezone($latitude, $longitude, $request->getQueryParam('timezonestring'), $this->model->locations);
    if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($timezone));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod);
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Please specify a valid latitude and longitude.', 400, 'Bad Request'), 400);
    }
});

$app->get('/timingsByAddress', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
    }
});

$app->get('/timingsByAddress/{timestamp}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $address = $request->getQueryParam('address');
    $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
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
    $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
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
    $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($locInfo) {
        $pt = new PrayerTimes($method, $school, null);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        $d = new DateTime(date('@' . $timestamp));
        $d->setTimezone(new DateTimeZone($locInfo['timezone']));
        if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
            $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
        }
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
        return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
    }
});

$app->get('/calendar', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
    $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
    $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
    $month = ApiRequest::month($request->getQueryParam('month'));
    $year = ApiRequest::year($request->getQueryParam('year'));
    $latitude = $request->getQueryParam('latitude');
    $longitude = $request->getQueryParam('longitude');
    $annual = ApiRequest::annual($request->getQueryParam('annual'));
    $timezone = $request->getQueryParam('timezonestring');
    if ($timezone == '' || $timezone  === null) {
        // Compute it.
        $timezone = $this->model->locations->getTimezoneByCoOrdinates($latitude, $longitude);
    }

    if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
        $pt = new PrayerTimes($method, $school);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
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
    $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
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
    $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
    $annual = ApiRequest::annual($request->getQueryParam('annual'));

    if ($locInfo) {
        $pt = new PrayerTimes($method, $school);
        if ($method == PrayerTimes::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2], $methodSettings[3], $methodSettings[4], $methodSettings[5], $methodSettings[6], $methodSettings[7], $methodSettings[8], $methodSettings[9], $methodSettings[10], $methodSettings[11]);
            $pt->setCustomMethod($customMethod);
        }
        if ($annual) {
            $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        } else {
            $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt);
        }
        return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country pair.', 400, 'Bad Request'), 400);
    }
});

$app->get('/cityInfo', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $city = $request->getQueryParam('city');
    $country = $request->getQueryParam('country');
    $state = $request->getQueryParam('state');
    $result = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find city and country.', 400, 'Bad Request'), 400);
    }
});

$app->get('/addressInfo', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $address = $request->getQueryParam('address');
    $result = $this->model->locations->getAddressCoOrdinatesAndZone($address);
    if ($result) {
        return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
    } else {
        return $response->withJson(ApiResponse::build('Unable to find address.', 400, 'Bad Request'), 400);
    }
});
