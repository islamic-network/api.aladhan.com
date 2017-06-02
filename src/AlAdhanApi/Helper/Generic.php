<?php

namespace AlAdhanApi\Helper;

class Generic
{
    
    /**
       * Timezones list with GMT offset
       *
       * @return array
       * @link http://stackoverflow.com/a/9328760
     */
    public static function tz_list() {
    $zones_array = array();
    $timestamp = time();
        
    foreach(timezone_identifiers_list() as $key => $zone) {
      date_default_timezone_set($zone);
      $zones_array[$key]['zone'] = $zone;
      $zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp))/100;
      $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
    }
        
    return $zones_array;
    }
    
    public static function isTimeZoneValid($timezone)
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC));
    }

    public static function addTimezoneAbbreviation(array $timings, \DateTime $date)
    {
        foreach ($timings as $key => $time) {
            $timings[$key] = $time . ' ('. $date->format('T') . ')';
        }

        return $timings;
    }

    public static function getTimeZoneOffsetString($timezoneString, \DateTime $date) {
        $tz = new \DateTimeZone($timezoneString);
        return $tz->getOffset($date)/3600;
    }
    
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
            $timings = self::addTimezoneAbbreviation($timings, $calstart);
            $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U')];
            $times[$i] =  ['timings' => $timings, 'date' => $date];
            // Add 24 hours to start date
            $cal_start =  $cal_start + (1*60*60*24);
        }

        return $times;
    }
    
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
                $timings = self::addTimezoneAbbreviation($timings, $calstart);
                $date = ['readable' => $calstart->format('d M Y'), 'timestamp' => $calstart->format('U')];
                $times[$month][$i] =  ['timings' => $timings, 'date' => $date];
                // Add 24 hours to start date
                $cal_start =  $cal_start + (1*60*60*24);
            }
        }
        
        return $times;
    }

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
