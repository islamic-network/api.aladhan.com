<?php
namespace AlAdhanApi\Helper;

class PrayerTimesHelper
{
    /**
     * Returns the next prayer time
     * @param  Array $timings
     * @param  PrayerTimes $pt
     * @param  DateTime $d
     * @param  Array $locInfo
     * @param  Integer $latitudeAdjustmentMethod
     * @return Array
     */
    public static function nextPrayerTime($timings, $pt, $d, $locInfo, $latitudeAdjustmentMethod)
    {
        $currentHour = date('H');
        $currentMinute = date('i');
        $currentTime = $currentHour . ':' . $currentMinute;
        $timestamps = [];
        $nextPrayer = null;
        foreach ($timings as $p => $t) {
            if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                $time = explode(':', $t);
                $prayerTime = new \DateTime(date("Y-m-d $time[0]:$time[1]:00"));
                $prayerTime->setTimezone(new \DateTimeZone($locInfo['timezone']));
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
            $d->setTime('00','01','01');
            $timings2 = $pt->getTimes($d, $locInfo['latitude'], $locInfo['longitude'], null, $latitudeAdjustmentMethod);
            foreach ($timings2 as $p => $t) {
                if (in_array($p, ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'])) {
                    $time = explode(':', $t);
                    $date = $d->format('Y-m-d');
                    $prayerTime = new \DateTime(date("$date $time[0]:$time[1]:00"));
                    $prayerTime->setTimezone(new \DateTimeZone($locInfo['timezone']));
                    $ts = $timestamps[$p] = $prayerTime->getTimestamp();
                    if ($ts > $d->getTimestamp()) {
                        $nextPrayer = [$p => $t];
                        break;
                    }
                }
            }
        }

        return $nextPrayer;
    }

    /**
     * Calculate Prayer Times for a complete month
     * @param  String $latitude
     * @param  String $longitude
     * @param  Integer $month
     * @param  Integer $year
     * @param  String $timezone
     * @param  Integer $latitudeAdjustmentMethod
     * @param  PrayerTimes Object $pt
     * @return Array
     */
    public static function calculateMonthPrayerTimes($latitude, $longitude, $month, $year, $timezone, $latitudeAdjustmentMethod, $pt)
    {

        $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
        $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);
        $times = [];

        for ($i = 0; $i <= ($days_in_month -1); $i++) {
            // Create date time object for this date.
            $calstart = new \DateTime( date('Y-m-d H:i:s', $cal_start) , new \DateTimeZone($timezone));
            if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart)) {
                $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
            }
            $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod);
            $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U')];
            $times[$i] =  ['timings' => $timings, 'date' => $date];
            // Add 24 hours to start date
            $cal_start =  $cal_start + (1*60*60*24);
        }

        return $times;
    }

    /**
     * Calculate Prayer Times for a complete year
     * @param  String $latitude
     * @param  String $longitude
     * @param  Integer $year
     * @param  String $timezone
     * @param  Integer $latitudeAdjustmentMethod
     * @param  PrayerTimes Object $pt
     * @return Array
     */
    public static function calculateYearPrayerTimes($latitude, $longitude, $year, $timezone, $latitudeAdjustmentMethod, $pt) {
        $times = [];
        for ($month=0; $month<=12; $month++) {
            $cal_start = strtotime($year . '-' . $month . '-01 09:01:01');
            $days_in_month = cal_days_in_month(\CAL_GREGORIAN, $month, $year);

            for ($i = 0; $i <= ($days_in_month -1); $i++) {
                // Create date time object for this date.
                $calstart = new \DateTime( date('Y-m-d H:i:s', $cal_start) , new \DateTimeZone($timezone));
                if ($pt->getMethod() == 'MAKKAH' && self::isRamadan($calstart)) {
                    $pt->tune(0, 0, 0, 0, 0, 0, 0, '30 min', 0);
                }
                $timings = $pt->getTimes($calstart, $latitude, $longitude, null, $latitudeAdjustmentMethod);
                $timings = Generic::addTimezoneAbbreviation($timings, $calstart);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U')];
                $times[$month][$i] =  ['timings' => $timings, 'date' => $date];
                // Add 24 hours to start date
                $cal_start =  $cal_start + (1*60*60*24);
            }
        }

        return $times;
    }

    /**
     * Checks if the given date falls in Ramadan
     * @param  DateTime $date
     * @return boolean
     */
    public static function isRamadan(\DateTime $date)
    {
        $hs = new \AlAdhanApi\HijriCalendarService();
        $hijDate = $hs->gToH($date->format('d') . '-' . $date->format('m') . '-' . $date->format('Y'));
        if ($hijDate['hijri']['month']['number'] == 9) {
            return true;
        }

        return false;
    }
}
