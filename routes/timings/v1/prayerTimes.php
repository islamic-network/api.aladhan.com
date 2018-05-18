<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Meezaan\PrayerTimes\PrayerTimes;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\PrayerTimesHelper;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Model\HijriCalendarService;

$app->group('/v1', function() {
    /**
     * @api {get} http://api.aladhan.com/v1/methods Prayer Times Methods
     * @apiDescription  Returns all the prayer times calculation methods supported by this API. For more information on how to use custom methods, see <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>.
     * @apiName GetMethods
     * @apiGroup Miscellaneous
     * @apiVersion 1.0.1
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/methods
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     "code": 200,
     *     "status": "OK",
     *     "data": {
     *         "MWL": {
     *             "id": 3,
     *             "name": "Muslim World League",
     *             "params": {
     *                 "Fajr": 18,
     *                 "Isha": 17
     *             }
     *         },
     *         "ISNA": {
     *             "id": 2,
     *             "name": "Islamic Society of North America (ISNA)",
     *             "params": {
     *                 "Fajr": 15,
     *                 "Isha": 15
     *             }
     *         },
     *         .... More methods
     *         "CUSTOM": {
     *             "id": 99
     *         }
     *     }
     * }
     *
     **/
    $this->get('/methods', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $pt = new PrayerTimes();

        return $response->withJson(ApiResponse::build($pt->getMethods(), 200, 'OK'), 200);
    });

    $this->get('/nextPrayerByAddress', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime('@' . time());
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode);
            $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
            return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });

    $this->get('/nextPrayerByAddress/{timestamp}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode);
            $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
            return $response->withJson(ApiResponse::build(['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });


    /**
     * @api {get} http://api.aladhan.com/v1/timings/:date_or_timestamp Timings
     * @apiDescription Returns all prayer times for a specific date.
     * @apiName GetTimings
     * @apiGroup Timings
     * @apiVersion 1.0.1
     *
     * @apiParam {string} [date_or_timestamp = 'now'] A date in the DD-MM-YYYY format or UNIX timestamp. Default's to the current date.
     * @apiParam {decimal} latitude The decimal value for the latitude co-ordinate of the location you want the time computed for. Example: 51.75865125
     * @apiParam {decimal} longitude The decimal value for the longitude co-ordinate of the location you want the time computed for. Example: -1.25387785
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/timings/1398332113?latitude=51.508515&longitude=-0.1254872&method=2
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     * }
     */
    $this->get('/timings', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $timezone = Generic::computeTimezone($latitude, $longitude, $request->getQueryParam('timezonestring'), $this->model->locations);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime('now', new DateTimeZone($timezone));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimesForToday($latitude, $longitude, $timezone, null, $latitudeAdjustmentMethod, $midnightMode);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid latitude and longitude.', 400, 'Bad Request'), 400);
        }
    });

    $this->get('/timings/{timestamp}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $timezone = Generic::computeTimezone($latitude, $longitude, $request->getQueryParam('timezonestring'), $this->model->locations);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($timezone));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode);
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid latitude and longitude.', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1//timingsByAddress/:date_or_timestamp Timings By Address
     * @apiDescription Returns all prayer times for a specific date at a particular address.
     * @apiName GetTimingsByAddresss
     * @apiGroup Timings
     * @apiVersion 1.0.1
     *
     * @apiParam {string} [date_or_timestamp = 'now'] A date in the DD-MM-YYYY format or UNIX timestamp. Default's to the current date.
     * @apiParam {string} address An address string. Example: 1420 Austin Bluffs Parkway, Colorado Springs, CO OR 25 Hampstead High Street, London, NW3 1RL, United Kingdom OR Sultanahmet Mosque, Istanbul, Turkey
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/timingsByAddress?address=Regents Park Mosque, London, UK
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     * }
     */
    $this->get('/timingsByAddress', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }

            $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod, $midnightMode);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });

    $this->get('/timingsByAddress/{timestamp}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/timingsByCity/:date_or_timestamp Timings By City
     * @apiDescription Returns all prayer times for a specific date in a particular city.
     * @apiName GetTimingsByCity
     * @apiGroup Timings
     * @apiVersion 1.0.1
     *
     * @apiParam {string} [date_or_timestamp = 'now'] A date in the DD-MM-YYYY format or UNIX timestamp. Default's to the current date.
     * @apiParam {string} city A city name. Example: London
     * @apiParam {string} country A country name or 2 character alpha ISO 3166 code. Examples: GB or United Kindom
     * @apiParam {string} [state] State or province. A state name or abbreviation. Examples: Colorado / CO / Punjab / Bengal
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/timingsByCity?city=Dubai&country=United Arab Emirates&method=8
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     * }
     */
    $this->get('/timingsByCity', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod, $midnightMode);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });

    $this->get('/timingsByCity/{timestamp}', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($locInfo) {
            $pt = new PrayerTimes($method, $school, null);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            if ($pt->getMethod() == 'MAKKAH' && PrayerTimesHelper::isRamadan($d)) {
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
            }
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'));
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return $response->withJson(ApiResponse::build(['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/calendar Prayer Times Calendar
     * @apiDescription Returns all prayer times for a specific calendar month.
     * @apiName GetCalendar
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {decimal} latitude The decimal value for the latitude co-ordinate of the location you want the time computed for. Example: 51.75865125
     * @apiParam {decimal} longitude The decimal value for the longitude co-ordinate of the location you want the time computed for. Example: -1.25387785
     * @apiParam {number=1-12} month A gregorian calendar month. Example: 8 or 08 for August.
     * @apiParam {number} year A gregorian calendar year. Example: 2014.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/calendar?latitude=51.508515&longitude=-0.1254872&method=2&month=4&year=2017
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/calendar', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $timezone = $request->getQueryParam('timezonestring');
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($timezone == '' || $timezone  === null) {
            // Compute it.
            $timezone = $this->model->locations->getTimezoneByCoOrdinates($latitude, $longitude);
        }

        if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/hijriCalendar Prayer Times Hijri Calendar
     * @apiDescription Returns all prayer times for a specific Hijri calendar month.
     * @apiName GetHijriCalendar
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {decimal} latitude The decimal value for the latitude co-ordinate of the location you want the time computed for. Example: 51.75865125
     * @apiParam {decimal} longitude The decimal value for the longitude co-ordinate of the location you want the time computed for. Example: -1.25387785
     * @apiParam {number=1-12} month A Hijri calendar month. Example: 9 or 09 for Ramadan.
     * @apiParam {number} year A Hijri calendar year. Example: 1437.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hijriCalendar?latitude=51.508515&longitude=-0.1254872&method=2&month=4&year=1437
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/hijriCalendar', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $timezone = $request->getQueryParam('timezonestring');
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        if ($timezone == '' || $timezone  === null) {
            // Compute it.
            $timezone = $this->model->locations->getTimezoneByCoOrdinates($latitude, $longitude);
        }

        if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid latitude, longitude and timezone.', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/calendarByAddress Prayer Times Calendar by address
     * @apiDescription Returns all prayer times for a specific calendar month at a particular address.
     * @apiName GetCalendarByAddress
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} address An address string. Example: 1420 Austin Bluffs Parkway, Colorado Springs, CO OR 25 Hampstead High Street, London, NW3 1RL, United Kingdom OR Sultanahmet Mosque, Istanbul, Turkey
     * @apiParam {number=1-12} month A gregorian calendar month. Example: 8 or 08 for August.
     * @apiParam {number} year A gregorian calendar year. Example: 2014.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/calendarByAddress?address=Sultanahmet Mosque, Istanbul, Turkey&method=2&month=04&year=2017
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/calendarByAddress', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));

        if ($locInfo) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid address.', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/hijriCalendarByAddress Prayer Times Hijri Calendar by address
     * @apiDescription Returns all prayer times for a specific Hijri calendar month at a particular address.
     * @apiName GetHijriCalendarByAddress
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} address An address string. Example: 1420 Austin Bluffs Parkway, Colorado Springs, CO OR 25 Hampstead High Street, London, NW3 1RL, United Kingdom OR Sultanahmet Mosque, Istanbul, Turkey
     * @apiParam {number=1-12} month A Hijri calendar month. Example: 9 or 09 for August.
     * @apiParam {number} year A Hijri calendar year. Example: 1437.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hijriCalendarByAddress?address=Sultanahmet Mosque, Istanbul, Turkey&method=2&month=04&year=1437
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/hijriCalendarByAddress', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $address = $request->getQueryParam('address');
        $locInfo = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));

        if ($locInfo) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Please specify a valid address.', 400, 'Bad Request'), 400);
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/calendarByCity Prayer Times Calendar by city
     * @apiDescription Returns all prayer times for a specific calendar month by City.
     * @apiName GetCalendarByCitys
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} city A city name. Example: London
     * @apiParam {string} country A country name or 2 character alpha ISO 3166 code. Examples: GB or United Kindom
     * @apiParam {string} [state] State or province. A state name or abbreviation. Examples: Colorado / CO / Punjab / Bengal
     * @apiParam {number=1-12} month A gregorian calendar month. Example: 8 or 08 for August.
     * @apiParam {number} year A gregorian calendar year. Example: 2014.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/calendarByCity?city=London&country=United Kingdom&method=2&month=04&year=2017
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/calendarByCity', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));

        if ($locInfo) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to find city and country pair.', 400, 'Bad Request'), 400);
        }
    });


    /**
     * @api {get} http://api.aladhan.com/v1/hijriCalendarByCity Prayer Times Hijri Calendar by city
     * @apiDescription Returns all prayer times for a specific Hijri calendar month by City.
     * @apiName GetHijriCalendarByCity
     * @apiGroup Calendar
     * @apiVersion 1.0.1
     *
     * @apiParam {string} city A city name. Example: London
     * @apiParam {string} country A country name or 2 character alpha ISO 3166 code. Examples: GB or United Kindom
     * @apiParam {string} [state] State or province. A state name or abbreviation. Examples: Colorado / CO / Punjab / Bengal
     * @apiParam {number=1-12} month A Hijri calendar month. Example: 9 or 09 for Ramadan.
     * @apiParam {number} year A Hijri calendar year. Example: 1437.
     * @apiParam {boolean} [annual=false] If true, we'll ignore the month and return the calendar for the whole year.
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,99} [method=2] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. This parameter accepts values from 0-12 and 99, as specified below:<br />
     *                               0 - Shia Ithna-Ansari<br />
     *                               1 - University of Islamic Sciences, Karachi<br />
     *                               2 - Islamic Society of North America<br />
     *                               3 - Muslim World League<br />
     *                               4 - Umm Al-Qura University, Makkah <br />
     *                               5 - Egyptian General Authority of Survey<br />
     *                               7 - Institute of Geophysics, University of Tehran<br />
     *                               8 - Gulf Region<br />
     *                               9 - Kuwait<br />
     *                               10 - Qatar<br />
     *                               11 - Majlis Ugama Islam Singapura, Singapore<br />
     *                               12 - Union Organization islamic de France<br />
     *                               13 - Diyanet İşleri Başkanlığı, Turkey<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/hijriCalendarByCity?city=London&country=United Kingdom&method=2&month=04&year=1437
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *    "code": 200,
     *    "status": "OK",
     *    "data": [{
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     },
     *     {
     *        "timings": {
     *            "Fajr": "03:57",
     *            "Sunrise": "05:46",
     *            "Dhuhr": "12:59",
     *            "Asr": "16:55",
     *            "Sunset": "20:12",
     *            "Maghrib": "20:12",
     *            "Isha": "22:02",
     *            "Imsak": "03:47",
     *            "Midnight": "00:59"
     *        },
     *        "date": {
     *            "readable": "24 Apr 2014",
     *            "timestamp": "1398332113",
     *            "gregorian": {
     *                "date": "15-05-2018",
     *                "format": "DD-MM-YYYY",
     *                "day": "15",
     *                "weekday": {
     *                    "en": "Tuesday"
     *                },
     *                "month": {
     *                    "number": 5,
     *                    "en": "May",
     *                },
     *                "year": "2018",
     *                "designation": {
     *                    "abbreviated": "AD",
     *                    "expanded": "Anno Domini",
     *                },
     *            },
     *            "hijri": {
     *                "date": "01-09-1439",
     *                "format": "DD-MM-YYYY",
     *                "day": "01",
     *                "weekday": {
     *                    "en": "Al Thalaata",
     *                    "ar": "الثلاثاء",
     *                },
     *                "month": {
     *                    "number": 9,
     *                    "en": "Ramaḍān",
     *                    "ar": "رَمَضان",
     *                },
     *                "year": "1439",
     *                "designation": {
     *                    "abbreviated": "AH",
     *                    "expanded": "Anno Hegirae",
     *                },
     *                "holidays": [
     *                    "1st Day of Ramadan"
     *                ],
     *            },
     *        },
     *        "meta": {
     *            "latitude": 51.508515,
     *            "longitude": -0.1254872,
     *            "timezone": "Europe/London",
     *            "method": {
     *                "id": 2,
     *                "name": "Islamic Society of North America (ISNA)",
     *                "params": {
     *                    "Fajr": 15,
     *                    "Isha": 15
     *                }
     *            },
     *            "latitudeAdjustmentMethod": "ANGLE_BASED",
     *            "midnightMode": "STANDARD",
     *            "school": "STANDARD",
     *            "offset": {
     *                "Imsak": 0,
     *                "Fajr": 0,
     *                "Sunrise": 0,
     *                "Dhuhr": 0,
     *                "Asr": 0,
     *                "Maghrib": 0,
     *                "Sunset": 0,
     *                "Isha": 0,
     *                "Midnight": 0
     *             }
     *         }
     *     }
     *     ... // More data here till the end of the month
     *     ]
     * }
     */
    $this->get('/hijriCalendarByCity', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method')));
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $locInfo = $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));

        if ($locInfo) {
            $pt = new PrayerTimes($method, $school);
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
            if ($method == PrayerTimes::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to find city and country pair.', 400, 'Bad Request'), 400);
        }
    });

    $this->get('/cityInfo', function (Request $request, Response $response) {
        //$this->helper->logger->write();
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

    $this->get('/addressInfo', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $address = $request->getQueryParam('address');
        $result = $this->model->locations->getAddressCoOrdinatesAndZone($address);
        if ($result) {
            return $response->withJson(ApiResponse::build($result, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to find address.', 400, 'Bad Request'), 400);
        }
    });
});
