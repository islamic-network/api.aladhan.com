<?php
namespace AlAdhanApi\Helper;
use IslamicNetwork\PrayerTimes\Method;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use AlAdhanApi\Model\HijriCalendarService;
use AlAdhanApi\Helper\Request as ApiRequest;
use IslamicNetwork\MoonSighting\Isha;
use DateTime;

/**
 * Class PrayerTimesHelper
 * @package Helper\PrayerTimesHelper
 */
class PrayerTimesHelper
{
    /**
     * @param $timings
     * @param $pt
     * @param $d
     * @param $locInfo
     * @param $latitudeAdjustmentMethod
     * @return array|null
     * @throws \Exception
     */
    public static function nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod, $iso8601)
    {
        $currentHour = date('H');
        $currentMinute = date('i');
        $currentTime = $currentHour . ':' . $currentMinute;
        $timestamps = [];
        $nextPrayer = null;
        // Recalculate timings without iso8601 so this calculation works
        $timings = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
        foreach ($timings as $p => $t) {
            if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                $time = explode(':', $t);
                $prayerTime = new \DateTime(date("Y-m-d $time[0]:$time[1]:00"), new \DateTimeZone($locInfo['timezone']));
                $ts = $timestamps[$p] = $prayerTime->getTimestamp();
                if ($ts > $d->getTimestamp()) {
                    $nextPrayer = [$p => $t];
                    break;
                }
            }
        }

        if ($nextPrayer == null) {
            $interval = new \DateInterval('P1D');
            $d->add($interval);
            $d->setTime('00', '01', '01');
            $timings2 = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
            foreach ($timings2 as $p => $t) {
                if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                    $time = explode(':', $t);
                    $date = $d->format('Y-m-d');
                    $prayerTime = new \DateTime(date("$date $time[0]:$time[1]:00"), new \DateTimeZone($locInfo['timezone']));
                    $ts = $timestamps[$p] = $prayerTime->getTimestamp();
                    if ($ts > $d->getTimestamp()) {
                        $nextPrayer = [$p => $t];
                        break;
                    }
                }
            }
        }
        if ($iso8601 == PrayerTimes::TIME_FORMAT_ISO8601) {
            $dateForIso = $d;
            $dateForIso->setTime($time[0], $time[1]);
            $time = $dateForIso->format(DateTime::ATOM);
            $nextPrayer[$p] = $time;
        }


        return $nextPrayer;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $month
     * @param $year
     * @param $timezone
     * @param $latitudeAdjustmentMethod
     * @param $pt
     * @param string $midnightMode
     * @param $adjustment
     * @return array
     * @throws \Exception
     */
    public static function calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H)
    {

        $cs = new HijriCalendarService();

        $hm = $cs->getGtoHCalendar($month, $year, $adjustment);
        $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
        $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);
        $times = [];

        for ($i = 0; $i <= ($days_in_month -1); $i++) {
            // Create date time object for this date.
            $calstart = new \DateTime( date('Y-m-d H:i:s', $cal_start), new \DateTimeZone($timezone));
            if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart, $adjustment)) {
                if ($tune !== null) {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
                } else {
                    $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
                }
            } else {
                if ($tune !== null) {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                }
            }
            $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $timeFormat);
            $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $hm[$i]['gregorian'], 'hijri' => $hm[$i]['hijri']];
            $times[$i] =  ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt)];
            // Add 24 hours to start date
            $cal_start =  $cal_start + (1*60*60*24);
        }

        return $times;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $month
     * @param $year
     * @param $timezone
     * @param $latitudeAdjustmentMethod
     * @param $pt
     * @param string $midnightMode
     * @param $adjustment
     * @return array
     * @throws \Exception
     */
    public static function calculateHijriMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H)
    {
        $cs = new HijriCalendarService();

        $hm = $cs->getHtoGCalendar($month, $year, $adjustment);

        $times = [];

        foreach ($hm as $key => $i) {
            // Create date time object for this date.
            $calstart = new \DateTime( date('Y-m-d H:i:s', strtotime($i['gregorian']['year']. '-' . $i['gregorian']['month']['number'] . '-' . $i['gregorian']['day']. ' 09:01:01')), new \DateTimeZone($timezone));
            if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart, $adjustment)) {
                if ($tune !== null) {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
                } else {
                    $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
                }
            }
            else {
                if ($tune !== null) {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                }
            }
            $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $timeFormat);
            $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $i['gregorian'], 'hijri' => $i['hijri']];
            $times[$key] =  ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt)];
        }

        return $times;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $year
     * @param $timezone
     * @param $latitudeAdjustmentMethod
     * @param $pt
     * @param string $midnightMode
     * @para $adjustment
     * @return array
     * @throws \Exception
     */
    public static function calculateHijriYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H)
    {
        $cs = new HijriCalendarService();
        $times = [];
        for ($month=0; $month<=12; $month++) {
            if ($month < 1) {
                $month = 1;
            }
            $hm = $cs->getHtoGCalendar($month, $year, $adjustment);

            foreach ($hm as $key => $i) {
                // Create date time object for this date.
            $calstart = new \DateTime( date('Y-m-d H:i:s', strtotime($i['gregorian']['year']. '-' . $i['gregorian']['month']['number'] . '-' . $i['gregorian']['day']. ' 09:01:01')), new \DateTimeZone($timezone));
                if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart, $adjustment)) {
                    if ($tune !== null) {
                        $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
                    } else {
                        $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
                    }
                }
                else {
                    if ($tune !== null) {
                        $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                    }
                }
                $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $timeFormat);
                $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $i['gregorian'], 'hijri' => $i['hijri']];
                $times[$month][$key] =  ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt)];
            }
        }

        return $times;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $year
     * @param $timezone
     * @param $latitudeAdjustmentMethod
     * @param $pt
     * @param string $midnightMode
     * @param $adjustment
     * @return array
     * @throws \Exception
     */
    public static function calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H)
    {
        $cs = new HijriCalendarService();
        $times = [];
        for ($month=0; $month<=12; $month++) {
            if ($month < 1) {
                $month = 1;
            }
            $hm = $cs->getGtoHCalendar($month, $year, $adjustment);
            $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
            $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);

            for ($i = 0; $i <= ($days_in_month -1); $i++) {
                // Create date time object for this date.
                $calstart = new \DateTime( date('Y-m-d H:i:s', $cal_start), new \DateTimeZone($timezone));
                if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart, $adjustment)) {
                    if ($tune !== null) {
                        $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
                    } else {
                        $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
                    }
                }
                else {
                    if ($tune !== null) {
                        $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                    }
                }
                $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $timeFormat);
                $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $hm[$i]['gregorian'], 'hijri' => $hm[$i]['hijri']];
                $times[$month][$i] =  ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt)];
                // Add 24 hours to start date
                $cal_start =  $cal_start + (1*60*60*24);
            }
        }

        return $times;
    }

    /**
     * @param \DateTime $date
     * @param int $adjustment
     * @return bool
     */
    public static function isRamadan(\DateTime $date, $adjustment = 0)
    {
        $hs = new \AlAdhanApi\Model\HijriCalendarService();
        $hijDate = $hs->gToH($date->format('d') . '-' . $date->format('m') . '-' . $date->format('Y'), $adjustment);
        if ($hijDate['hijri']['month']['number'] == 9) {
            return true;
        }

        return false;
    }

    public static function getMetaArray($prayerTimesModel)
    {
        return $prayerTimesModel->getMeta();
    }

    public static function createCustomMethod($fA = null, $mA = null, $iA = null)
    {
        $method = new Method('Custom');
        if ($fA !== null) {
            $method->setFajrAngle($fA);
        }
        if ($mA !== null) {
            $method->setMaghribAngleOrMins($mA);
        }
        if ($iA !== null) {
            $method->setIshaAngleOrMins($iA);
        }

        return $method;

    }

    /**
     * A Simple helper to reduce the repeated code in the routes file
     * @param  Request $request http request object
     * @param  DateTime $d       DateTime object
     * @param  int $method
     * @param  int $school
     * @param  array $tune
     * @param int $adjustment
     * @return PrayerTimes
     */
    public static function getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment = 0, $shafaq = Isha::SHAFAQ_GENERAL)
    {
        $pt = new PrayerTimes($method, $school, null);
        $pt->setShafaq($shafaq);

        if ($method == Method::METHOD_CUSTOM) {
            $methodSettings = ApiRequest::customMethod($request->getQueryParam('methodSettings'));
            $customMethod = self::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
            $pt->setCustomMethod($customMethod);
        }
        if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($d, $adjustment)) {
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], '30 min', $tune[8]);
        }
        else {
            $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
        }

        return $pt;

    }
}
