<?php

namespace AlAdhanApi\Helper;

/**
 * Class Generic
 * @package Helper\Generic
 */
class Generic
{

    /**
       * Timezones list with GMT offset
       *
       * @return array
       * @link http://stackoverflow.com/a/9328760
     */
    public static function tz_list()
    {
        $zones_array = array();
        $timestamp = time();

        foreach (timezone_identifiers_list() as $key => $zone) {
              date_default_timezone_set($zone);
              $zones_array[$key]['zone'] = $zone;
              $zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp))/100;
              $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return $zones_array;
    }

    /**
     * Checks if a timezone is valid
     * @param  String  $timezone Example: Europe/London
     * @return boolean
     */
    public static function isTimeZoneValid($timezone)
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC));
    }

    /**
     * Builds list of Timezone Abbreviations against times
     * @param array    $timings An Array containing prayer timings
     * @param DateTime $date
     */
    public static function addTimezoneAbbreviation(array $timings, \DateTime $date)
    {
        foreach ($timings as $key => $time) {
            $timings[$key] = $time . ' ('. $date->format('T') . ')';
        }

        return $timings;
    }

    /**
     * Get GMT offset for a timezone
     * @param String  $timezoneString
     * @param DateTime $date
     */
    public static function getTimeZoneOffsetString($timezoneString, \DateTime $date)
    {
        $tz = new \DateTimeZone($timezoneString);
        return $tz->getOffset($date)/3600;
    }

    /**
     * Checks if the current request is from Googlebot
     * @return boolean
     */
    public static function isGoogleBot()
    {
        if (
            isset($_SERVER['HTTP_USER_AGENT']) &&
            (
            strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') !== false ||
            strpos($_SERVER['HTTP_USER_AGENT'], 'YandexBot') !== false
            )
        ) {
            return true;
        }

        return false;
    }
}
