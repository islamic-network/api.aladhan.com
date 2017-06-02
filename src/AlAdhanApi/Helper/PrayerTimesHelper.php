<?php
namespace AlAdhanApi\Helper;

class PrayerTimesHelper
{
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


}
