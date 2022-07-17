<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\PrayerTimesHelper;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Model\HijriCalendarService;
use AlAdhanApi\Interceptor\CoOrdinates;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
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
    $group->get('/methods', function (Request $request, Response $response) {
        
        $pt = new PrayerTimes();

        return ApiResponse::print($response, $pt->getMethods(), 200, 'OK', true, 3600);
    });

    $group->get('/nextPrayerByAddress', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $address = ApiRequest::getQueryParam($request, 'address');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidAddress($address) ? $this->get('model')->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime('@' . time());
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod, $iso8601);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
            return ApiResponse::print($response, ['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
        }
    });

    $group->get('/nextPrayerByAddress/{timestamp}', function (Request $request, Response $response) {
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'));
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $address = ApiRequest::getQueryParam($request, 'address');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidAddress($address) ? $this->get('model')->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $nextPrayer = PrayerTimesHelper::nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod, $iso8601);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U')];
            return ApiResponse::print($response, ['timings' => $nextPrayer, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
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
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,14,15,99} [method] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. If not specified, it defaults to the closest authority based on the location or co-ordinates specified in the API call. This parameter accepts values from 0-12 and 99, as specified below:<br />     *                               0 - Shia Ithna-Ansari<br />
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
     *                               14 - Spiritual Administration of Muslims of Russia<br />
     *                               15 - Moonsighting Committee Worldwide (also requires shafaq paramteer)<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [shafaq=general] Which Shafaq to use if the method is Moonsighting Commitee Worldwide. Acceptable options are 'general', 'ahmer' and 'abyad'. Defaults to 'general'.
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     * @apiParam {boolean} [iso8601=false] Whether to return the prayer times in the iso8601 format. Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
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
    $group->get('/timings', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $latitude = ApiRequest::getQueryParam($request, 'latitude');
        $longitude = ApiRequest::getQueryParam($request, 'longitude');
        $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $latitude, $longitude));
        $timezone = Generic::computeTimezone($latitude, $longitude, ApiRequest::getQueryParam($request, 'timezonestring'), $this->get('model')->locations);
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        CoOrdinates::areValid($request, $latitude, $longitude);
        if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
            $d = new DateTime('now', new DateTimeZone($timezone));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimesForToday($latitude, $longitude, $timezone, null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Please specify a valid latitude and longitude.', 400, 'Bad Request');
        }
    });

    $group->get('/timings/{timestamp}', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $latitude = ApiRequest::getQueryParam($request, 'latitude');
        $longitude = ApiRequest::getQueryParam($request, 'longitude');
        $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $latitude, $longitude));
        $timezone = Generic::computeTimezone($latitude, $longitude, ApiRequest::getQueryParam($request, 'timezonestring'), $this->get('model')->locations);
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $timestamp = ApiRequest::time($request->getAttribute('timestamp'), $timezone);
        if (ApiRequest::isTimingsRequestValid($latitude, $longitude, $timezone)) {
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($timezone));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Please specify a valid latitude and longitude.', 400, 'Bad Request');
        }
    });

    /**
     * @api {get} http://api.aladhan.com/v1/timingsByAddress/:date_or_timestamp Timings By Address
     * @apiDescription Returns all prayer times for a specific date at a particular address.
     * @apiName GetTimingsByAddresss
     * @apiGroup Timings
     * @apiVersion 1.0.1
     *
     * @apiParam {string} [date_or_timestamp = 'now'] A date in the DD-MM-YYYY format or UNIX timestamp. Default's to the current date.
     * @apiParam {string} address An address string. Example: 1420 Austin Bluffs Parkway, Colorado Springs, CO OR 25 Hampstead High Street, London, NW3 1RL, United Kingdom OR Sultanahmet Mosque, Istanbul, Turkey
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,14,15,99} [method] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. If not specified, it defaults to the closest authority based on the location or co-ordinates specified in the API call. This parameter accepts values from 0-12 and 99, as specified below:<br />     *                               0 - Shia Ithna-Ansari<br />
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
     *                               14 - Spiritual Administration of Muslims of Russia<br />
     *                               15 - Moonsighting Committee Worldwide (also requires shafaq paramteer)<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [shafaq=general] Which Shafaq to use if the method is Moonsighting Commitee Worldwide. Acceptable options are 'general', 'ahmer' and 'abyad'. Defaults to 'general'.
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     * @apiParam {boolean} [iso8601=false] Whether to return the prayer times in the iso8601 format. Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
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
    $group->get('/timingsByAddress', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $address = ApiRequest::getQueryParam($request, 'address');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidAddress($address) ? $this->get('model')->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
        }
    });

    $group->get('/timingsByAddress/{timestamp}', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $address = ApiRequest::getQueryParam($request, 'address');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidAddress($address) ? $this->get('model')->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $timestamp = ApiRequest::time($request->getAttribute('timestamp'), $locInfo['timezone']);
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate address (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
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
     * @apiParam {number=0,1,2,3,4,5,7,8,9,10,11,12,13,14,15,99} [method] A prayer times calculation method. Methods identify various schools of thought about how to compute the timings. If not specified, it defaults to the closest authority based on the location or co-ordinates specified in the API call. This parameter accepts values from 0-12 and 99, as specified below:<br />     *                               0 - Shia Ithna-Ansari<br />
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
     *                               14 - Spiritual Administration of Muslims of Russia<br />
     *                               15 - Moonsighting Committee Worldwide (also requires shafaq paramteer)<br />
     *                               99 - Custom. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {string} [shafaq=general] Which Shafaq to use if the method is Moonsighting Commitee Worldwide. Acceptable options are 'general', 'ahmer' and 'abyad'. Defaults to 'general'.
     * @apiParam {string} [tune] Comma Separated String of integers to offset timings returned by the API in minutes. Example: 5,3,5,7,9,7. See <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>
     * @apiParam {number{0-1}} [school = 0] 0 for Shafi (or the standard way), 1 for Hanafi. If you leave this empty, it defaults to Shafii.
     * @apiParam {number{0-1}} [midnightMode = 0] 0 for Standard (Mid Sunset to Sunrise), 1 for Jafari (Mid Sunset to Fajr). If you leave this empty, it defaults to Standard.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     * @apiParam {boolean} [iso8601=false] Whether to return the prayer times in the iso8601 format. Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
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
    $group->get('/timingsByCity', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $city = ApiRequest::getQueryParam($request, 'city');
        $country = ApiRequest::getQueryParam($request, 'country');
        $state = ApiRequest::getQueryParam($request, 'state');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidLocationPair($city, $country) ? $this->get('model')->locations->getGoogleCoOrdinatesAndZone($city, $country, $state) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime('now', new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimesForToday($locInfo['latitude'], $locInfo['longitude'],$locInfo['timezone'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
        }
    });

    $group->get('/timingsByCity/{timestamp}', function (Request $request, Response $response) {
        $school = ClassMapper::school(ApiRequest::school(ApiRequest::getQueryParam($request, 'school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode(ApiRequest::getQueryParam($request, 'midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(ApiRequest::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $city = ApiRequest::getQueryParam($request, 'city');
        $country = ApiRequest::getQueryParam($request, 'country');
        $state = ApiRequest::getQueryParam($request, 'state');
        $tune = ApiRequest::tune(ApiRequest::getQueryParam($request, 'tune'));
        $adjustment = (int) ApiRequest::getQueryParam($request, 'adjustment');
        $iso8601 = ApiRequest::getQueryParam($request, 'iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq(ApiRequest::getQueryParam($request, 'shafaq'));
        $locInfo = ApiRequest::isValidLocationPair($city, $country) ? $this->get('model')->locations->getGoogleCoOrdinatesAndZone($city, $country, $state) : false;
        if ($locInfo) {
            $timestamp = ApiRequest::time($request->getAttribute('timestamp'), $locInfo['timezone']);
            $method = ClassMapper::method(ApiRequest::method(ApiRequest::getQueryParam($request, 'method'), $locInfo['latitude'], $locInfo['longitude']));
            $d = new DateTime(date('@' . $timestamp));
            $d->setTimezone(new DateTimeZone($locInfo['timezone']));
            $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment, $shafaq);
            $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod, $midnightMode, $iso8601);
            $cs = new HijriCalendarService();
            $hd = $cs->gToH($d->format('d-m-Y'), $adjustment);
            $date = ['readable' => $d->format('d M Y'), 'timestamp' => $d->format('U'), 'hijri' => $hd['hijri'], 'gregorian' => $hd['gregorian']];
            return ApiResponse::print($response, ['timings' => $timings, 'date' => $date, 'meta' => PrayerTimesHelper::getMetaArray($pt)], 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to locate city and country (even via google geocoding). It is probably invalid!', 400, 'Bad Request');
        }
    });

    $group->get('/cityInfo', function (Request $request, Response $response) {
        $city = ApiRequest::getQueryParam($request, 'city');
        $country = ApiRequest::getQueryParam($request, 'country');
        $state = ApiRequest::getQueryParam($request, 'state');
        $result = $this->get('model')->locations->getGoogleCoOrdinatesAndZone($city, $country, $state);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to find city and country.', 400, 'Bad Request');
        }
    });

    $group->get('/addressInfo', function (Request $request, Response $response) {
        $address = ApiRequest::getQueryParam($request, 'address');
        $result = $this->get('model')->locations->getAddressCoOrdinatesAndZone($address);
        if ($result) {
            return ApiResponse::print($response, $result, 200, 'OK');
        } else {
            return ApiResponse::print($response, 'Unable to find address.', 400, 'Bad Request');
        }
    });
});
