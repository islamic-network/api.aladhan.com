<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use IslamicNetwork\PrayerTimes\Method;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\ClassMapper;
use AlAdhanApi\Helper\PrayerTimesHelper;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Model\HijriCalendarService;
use AlAdhanApi\Interceptor\CoOrdinates;

$app->group('/v1', function() {
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
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     * @apiParam {boolean} [iso8601=false] Whether to return the prayer times in the iso8601 format. Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $latitude, $longitude));
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $timezone = $request->getQueryParam('timezonestring');
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        CoOrdinates::areValid($latitude, $longitude);

        if ($timezone == '' || $timezone  === null) {
            // Compute it.
            $timezone = $this->model->locations->getTimezoneByCoOrdinates($latitude, $longitude);
        }

        if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
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
     * @apiParam {string} [timezonestring] A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a>  . Example: Europe/London. If you do not specify this, we'll calcuate it using the co-ordinates you provide.
     * @apiParam {number} [latitudeAdjustmentMethod=3] Method for adjusting times higher latitudes - for instance, if you are checking timings in the UK or Sweden.<br />
     *                                                 1 - Middle of the Night<br />
     *                                                 2 - One Seventh<br />
     *                                                 3 - Angle Based<br />
     * @apiParam {number} adjustment Number of days to adjust hijri date(s). Example: 1 or 2 or -1 or -2
     * @apiParam {boolean} [iso8601=false] Whether to return the prayer times in the iso8601 format. Example: true will return 2020-07-01T02:56:00+01:00 instead of 02:56
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $latitude = $request->getQueryParam('latitude');
        $longitude = $request->getQueryParam('longitude');
        $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $latitude, $longitude));
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $timezone = $request->getQueryParam('timezonestring');
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        if ($timezone == '' || $timezone  === null) {
            // Compute it.
            $timezone = $this->model->locations->getTimezoneByCoOrdinates($latitude, $longitude);
        }

        if (ApiRequest::isCalendarRequestValid($latitude, $longitude, $timezone)) {
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $address = $request->getQueryParam('address');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $locInfo = ApiRequest::isValidAddress($address) ? $this->model->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $locInfo['latitude'], $locInfo['longitude']));
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $address = $request->getQueryParam('address');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $locInfo = ApiRequest::isValidAddress($address) ? $this->model->locations->getAddressCoOrdinatesAndZone($address) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $locInfo['latitude'], $locInfo['longitude']));
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::month($request->getQueryParam('month'));
        $year = ApiRequest::year($request->getQueryParam('year'));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $locInfo = ApiRequest::isValidLocationPair($city, $country) ? $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $locInfo['latitude'], $locInfo['longitude']));
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
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
        $school = ClassMapper::school(ApiRequest::school($request->getQueryParam('school')));
        $midnightMode = ClassMapper::midnightMode(ApiRequest::school($request->getQueryParam('midnightMode')));
        $latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod($request->getQueryParam('latitudeAdjustmentMethod')));
        $month = ApiRequest::hijriMonth($request->getQueryParam('month'));
        $year = ApiRequest::hijriYear($request->getQueryParam('year'));
        $city = $request->getQueryParam('city');
        $country = $request->getQueryParam('country');
        $state = $request->getQueryParam('state');
        $annual = ApiRequest::annual($request->getQueryParam('annual'));
        $tune = ApiRequest::tune($request->getQueryParam('tune'));
        $adjustment = (int) $request->getQueryParam('adjustment');
        $shafaq = ApiRequest::shafaq($request->getQueryParam('shafaq'));
        $iso8601 = $request->getQueryParam('iso8601') === 'true' ? PrayerTimes::TIME_FORMAT_ISO8601 : PrayerTimes::TIME_FORMAT_24H;
        $locInfo = ApiRequest::isValidLocationPair($city, $country) ? $this->model->locations->getGoogleCoOrdinatesAndZone($city, $country, $state) : false;
        if ($locInfo) {
            $method = ClassMapper::method(ApiRequest::method($request->getQueryParam('method'), $locInfo['latitude'], $locInfo['longitude']));
            $pt = new PrayerTimes($method, $school);
            $pt->setShafaq($shafaq);
            if ($method == Method::METHOD_CUSTOM) {
                $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
                $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
            }
            if ($annual) {
                $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            } else {
                $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($locInfo['latitude'], $locInfo['longitude'], $month, $year, $locInfo['timezone'], $latitudeAdjustmentMethod, $pt, $midnightMode, $adjustment, $tune, $iso8601);
            }
            return $response->withJson(ApiResponse::build($times, 200, 'OK'), 200);
        } else {
            return $response->withJson(ApiResponse::build('Unable to find city and country pair.', 400, 'Bad Request'), 400);
        }
    });

});
