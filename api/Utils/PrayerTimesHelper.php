<?php
namespace Api\Utils;
use Api\Models\HijriCalendar;
use DateInterval;
use IslamicNetwork\PrayerTimes\Method;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use Api\Utils\Request as ApiRequest;
use IslamicNetwork\MoonSighting\Isha;
use Mamluk\Kipchak\Components\Http;
use DateTime;
use DateTimeZone;

/**
 * Class PrayerTimesHelper
 * @package Helper\PrayerTimesHelper
 */
class PrayerTimesHelper
{
    /**
     * @param $pt
     * @param $d
     * @param $latitude
     * @param $longitude
     * @param $latitudeAdjustmentMethod
     * @param $iso8601
     * @param $timezone
     * @return array|null
     * @throws \Exception
     */
    public static function nextPrayerTime($pt, $d, $latitude, $longitude, $latitudeAdjustmentMethod, $iso8601, $timezone): ?array
    {
        $currentHour = date('H');
        $currentMinute = date('i');
        $currentTime = $currentHour . ':' . $currentMinute;
        $timestamps = [];
        $nextPrayer = null;
        // Recalculate timings without iso8601 so this calculation works
        $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod);
        $timeNow = new DateTime('now', new DateTimeZone($timezone));
        foreach ($timings as $p => $t) {
            if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                $time = explode(':', $t);
                $prayerTime = new DateTime(date("Y-m-d $time[0]:$time[1]:00"), new DateTimeZone($timezone));
                $ts = $timestamps[$p] = $prayerTime->getTimestamp();
                if ($ts > $timeNow->getTimestamp()) {
                    $nextPrayer = [$p => $t];
                    break;
                }
            }
        }

        if ($nextPrayer == null) {
            $interval = new \DateInterval('P1D');
            $d->add($interval);
            $d->setTime('00', '01', '01');
            $timings2 = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod);
            foreach ($timings2 as $p => $t) {
                if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                    $time = explode(':', $t);
                    $date = $d->format('Y-m-d');
                    $prayerTime = new DateTime(date("$date $time[0]:$time[1]:00"), new DateTimeZone($timezone));
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
     * @param PrayerTimes $pt
     * @param $midnightMode
     * @param $adjustment
     * @param $tune
     * @param $timeFormat
     * @param string|null $methodSettings
     * @param bool $enableMasking
     * @return array
     * @throws \Exception
     */
    public static function calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null, bool $enableMasking = true, $calendarMethod = HijriDate::CALENDAR_METHOD_HJCoSA): array
    {

        $cs = new HijriCalendar();

        $hm = $cs->getGtoHCalendar($month, $year, $calendarMethod, $adjustment);
        $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
        $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);
        $times = [];

        for ($i = 0; $i <= ($days_in_month - 1); $i++) {
            // Create date time object for this date.
            $calstart = new DateTime(date('Y-m-d H:i:s', $cal_start), new DateTimeZone($timezone));
            $timings = self::calculateTimings($calstart, $pt, $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode, $adjustment, $timeFormat, $methodSettings);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $hm[$i]['gregorian'], 'hijri' => $hm[$i]['hijri']];
            $times[$i] = ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt, $enableMasking)];
            // Add 24 hours to start date
            $cal_start = $cal_start + (1 * 60 * 60 * 24);
        }

        return $times;
    }

    public static function calculateRangePrayerTimes($latitude, $longitude, DateTime $start, DateTime $end, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null, bool $enableMasking = true, $calendarMethod = HijriDate::CALENDAR_METHOD_HJCoSA): array
    {
        $cs = new HijriCalendar();
        $times = [];
        $i = 0;
        while($start <= $end) {
            $timings = self::calculateTimings($start, $pt, $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode, $adjustment, $timeFormat, $methodSettings);
            $hm = $cs->gToH($start->format('d-m-Y'), $calendarMethod, $adjustment);
            $date = ['readable' => $start->format('d M Y'), 'timestamp' => $start->format('U'), 'gregorian' => $hm['gregorian'], 'hijri' => $hm['hijri']];
            $times[$i] = ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt, $enableMasking)];
            $start = $start->add(new DateInterval('P1D'));
            $i++;
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
     * @param PrayerTimes $pt
     * @param $midnightMode
     * @param $adjustment
     * @param $tune
     * @param $timeFormat
     * @param string|null $methodSettings
     * @param bool $enableMasking
     * @return array
     * @throws \Exception
     */
    public static function calculateHijriMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null, bool $enableMasking = true, $calendarMethod = HijriDate::CALENDAR_METHOD_HJCoSA): array
    {
        $cs = new HijriCalendar();
        if ($calendarMethod !== HijriDate::CALENDAR_METHOD_MATHEMATICAL) {
            $adjustment = 0;
        }

        $hm = $cs->getHtoGCalendar($month, $year, $calendarMethod, $adjustment);

        $times = [];

        foreach ($hm as $key => $i) {
            // Create date time object for this date.
            $calstart = new DateTime(date('Y-m-d H:i:s', strtotime($i['gregorian']['year'] . '-' . $i['gregorian']['month']['number'] . '-' . $i['gregorian']['day'] . ' 09:01:01')), new DateTimeZone($timezone));
            $timings = self::calculateTimings($calstart, $pt, $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode, $adjustment, $timeFormat, $methodSettings);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $i['gregorian'], 'hijri' => $i['hijri']];
            $times[$key] = ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt, $enableMasking)];
        }

        return $times;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $year
     * @param $timezone
     * @param $latitudeAdjustmentMethod
     * @param PrayerTimes $pt
     * @param $midnightMode
     * @param $adjustment
     * @param $tune
     * @param $timeFormat
     * @param string|null $methodSettings
     * @param bool $enableMasking
     * @return array
     * @throws \Exception
     */
    public static function calculateHijriYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null, bool $enableMasking = true, $calendarMethod = HijriDate::CALENDAR_METHOD_HJCoSA): array
    {
        $cs = new HijriCalendar();
        $times = [];
        for ($month = 0; $month <= 12; $month++) {
            if ($month < 1) {
                $month = 1;
            }
            if ($calendarMethod !== HijriDate::CALENDAR_METHOD_MATHEMATICAL) {
                $adjustment = 0;
            }
            $hm = $cs->getHtoGCalendar($month, $year, $calendarMethod, $adjustment);

            foreach ($hm as $key => $i) {
                // Create date time object for this date.
                $calstart = new DateTime(date('Y-m-d H:i:s', strtotime($i['gregorian']['year'] . '-' . $i['gregorian']['month']['number'] . '-' . $i['gregorian']['day'] . ' 09:01:01')), new DateTimeZone($timezone));
                $timings = self::calculateTimings($calstart, $pt, $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode, $adjustment, $timeFormat, $methodSettings);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $i['gregorian'], 'hijri' => $i['hijri']];
                $times[$month][$key] = ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt, $enableMasking)];
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
     * @param PrayerTimes $pt
     * @param $midnightMode
     * @param $adjustment
     * @param $tune
     * @param $timeFormat
     * @param string|null $methodSettings
     * @param bool $enableMasking
     * @return array
     * @throws \Exception
     */
    public static function calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, PrayerTimes $pt, $midnightMode = 'STANDARD', $adjustment = 0, $tune = null, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null, bool $enableMasking = true, $calendarMethod = HijriDate::CALENDAR_METHOD_HJCoSA): array
    {
        $cs = new HijriCalendar();
        $times = [];
        for ($month = 0; $month <= 12; $month++) {
            if ($month < 1) {
                $month = 1;
            }
            $hm = $cs->getGtoHCalendar($month, $year, $calendarMethod, $adjustment);
            $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
            $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);

            for ($i = 0; $i <= ($days_in_month - 1); $i++) {
                // Create date time object for this date.
                $calstart = new DateTime(date('Y-m-d H:i:s', $cal_start), new DateTimeZone($timezone));
                $timings = self::calculateTimings($calstart, $pt, $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode, $adjustment, $timeFormat, $methodSettings);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U'), 'gregorian' => $hm[$i]['gregorian'], 'hijri' => $hm[$i]['hijri']];
                $times[$month][$i] = ['timings' => $timings, 'date' => $date, 'meta' => self::getMetaArray($pt, $enableMasking)];
                // Add 24 hours to start date
                $cal_start = $cal_start + (1 * 60 * 60 * 24);
            }
        }

        return $times;
    }

    /**
     * @param DateTime $date
     * @param $adjustment
     * @return bool
     */
    public static function isRamadan(\DateTime $date, $adjustment = 0): bool
    {
        $hs = new HijriCalendar();
        $hijDate = $hs->gToH($date->format('d') . '-' . $date->format('m') . '-' . $date->format('Y'), $adjustment);
        if ($hijDate['hijri']['month']->number == 9) {
            return true;
        }

        return false;
    }

    /**
     * @param PrayerTimes $prayerTimesModel
     * @param bool $enableMasking
     * @return array
     */
    public static function getMetaArray(PrayerTimes $prayerTimesModel, bool $enableMasking = false): array
    {
        $meta = $prayerTimesModel->getMeta();

        if ($enableMasking) {
            $meta['latitude'] = (float) 8.8888888;
            $meta['longitude'] = (float) 7.7777777;
        }

        return $meta;
    }

    /**
     * @param $fA
     * @param $mA
     * @param $iA
     * @return Method
     */
    public static function createCustomMethod($fA = null, $mA = null, $iA = null): Method
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
     * @param $request
     * @param $d
     * @param $method
     * @param $school
     * @param $tune
     * @param $adjustment
     * @param $shafaq
     * @return PrayerTimes
     */
    public static function getAndPreparePrayerTimesObject($request, $d, $method, $school, $tune, $adjustment = 0, $shafaq = Isha::SHAFAQ_GENERAL): PrayerTimes
    {
        $pt = new PrayerTimes($method, $school, null);
        $pt->setShafaq($shafaq);
        $methodSettings = ApiRequest::customMethod(Http\Request::getQueryParam($request, 'methodSettings'));

        return self::applyMethodSpecificTuning($pt, $tune, $d, $adjustment, $methodSettings);
    }

    /**
     * @param PrayerTimes $pt
     * @param array|null $tune
     * @param DateTime $d
     * @param $adjustment
     * @param array|null $methodSettings
     * @return PrayerTimes
     */
    public static function applyMethodSpecificTuning(PrayerTimes $pt, ?array $tune, DateTime $d, $adjustment = 0, ?array $methodSettings = null): PrayerTimes
    {
        $method = $pt->getMethod();

        $tune = $tune ?? [0, 0, 0, 0, 0, 0, 0, 0, 0];

        $methodSettings = ($methodSettings === null || (!isset($methodSettings[0]) && !isset($methodSettings[1]) && !isset($methodSettings[2])))
            ? [null, null, null]
            : $methodSettings;

        switch ($method) {
            case Method::METHOD_CUSTOM:
                $customMethod = self::createCustomMethod($methodSettings[0], $methodSettings[1], $methodSettings[2]);
                $pt->setCustomMethod($customMethod);
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                break;
            case Method::METHOD_MAKKAH:
                if (self::isRamadan($d, $adjustment)) {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], intval('30 min'), $tune[8]);
                } else {
                    $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                }
                break;
            case Method::METHOD_DUBAI:
                $pt->tune($tune[0], $tune[1], $tune[2], 3, $tune[4], 3, 3, $tune[7], $tune[8]);
                break;
            case Method::METHOD_TURKEY:
                $pt->tune($tune[0], $tune[1], $tune[2] === 0 ? -7 : $tune[2], $tune[3] === 0 ? 5 : $tune[3], $tune[4] === 0 ? 4 : $tune[4], $tune[5] === 0 ? 7 : $tune[5], $tune[6] === 0 ? 7 : $tune[6], $tune[7], $tune[8]);
                break;
            case Method::METHOD_MOROCCO:
                $pt->tune($tune[0], $tune[1], $tune[2], 5, $tune[4], 5, $tune[6], $tune[7], $tune[8]);
                break;
            case Method::METHOD_PORTUGAL:
                $pt->tune($tune[0], $tune[1], $tune[2], 5, $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                break;
            default:
                $pt->tune($tune[0], $tune[1], $tune[2], $tune[3], $tune[4], $tune[5], $tune[6], $tune[7], $tune[8]);
                break;
        }

        return $pt;
    }

    /**
     * @param DateTime $d
     * @param PrayerTimes $pt
     * @param array|null $tune
     * @param $latitude
     * @param $longitude
     * @param $latitudeAdjustmentMethod
     * @param $midnightMode
     * @param $adjustment
     * @param $timeFormat
     * @param string|null $methodSettings
     * @return array
     */
    private static function calculateTimings(DateTime $d, PrayerTimes $pt, ?array $tune, $latitude, $longitude, $latitudeAdjustmentMethod, $midnightMode = 'STANDARD', $adjustment = 0, $timeFormat = PrayerTimes::TIME_FORMAT_24H, string $methodSettings = null): array
    {
        $methodSettings = ApiRequest::customMethod($methodSettings);
        $pt = self::applyMethodSpecificTuning($pt, $tune, $d, $adjustment, $methodSettings);
        $timings = $pt->getTimes($d, $latitude, $longitude, null, $latitudeAdjustmentMethod, $midnightMode, $timeFormat);

        if ($timeFormat !== PrayerTimes::TIME_FORMAT_ISO8601) {
            return Timezone::addTimezoneAbbreviation($timings, $d);
        }

        return $timings;
    }

}
