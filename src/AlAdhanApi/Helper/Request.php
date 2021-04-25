<?php

namespace AlAdhanApi\Helper;

use AlAdhanApi\Model\HijriCalendarService;
use IslamicNetwork\MoonSighting\Isha;
use Emoji;

/**
 * Class Request
 * @package Helper\Request
 */
class Request
{

    public static function school($data): int
    {
        if (in_array($data, [0, 1])) {
            return (int)$data;
        }

        return (int) 0;
    }

    public static function shafaq($data): string
    {
        if (in_array($data, [Isha::SHAFAQ_GENERAL, Isha::SHAFAQ_AHMER, Isha::SHAFAQ_ABYAD])) {
            return $data;
        }

        return Isha::SHAFAQ_GENERAL;
    }

    public static function midnightMode($data):int
    {
        if (in_array($data, [0, 1])) {
            return $data;
        }

        return 0;
    }

    public static function latitudeAdjustmentMethod($data): int
    {
        if (!in_array($data, [1, 2, 3])) {
            return 3;
        }

        return $data;
    }

    public static function method($data): int
    {
        if ($data == 'null' || $data == '') {
            return 2; //ISNA;
        }
        if (!in_array($data, [0, 1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15, 99])) {
            return 2; // ISNA
        } else {
            return (int) $data;
        }
    }

    public static function customMethod($data): array
    {
        $method = explode(',', $data);
        $result = [];
        // PrayerTimesHelper::createCustomMethod() takes a total of 12 params
        for ($i=0; $i<=11; $i++) {
            if (isset($method[$i]) && $method[$i] != 0 && $method[$i] != null) {
                $result[] = (string) $method[$i];
            } else {
                $result[] = null;
            }
        }

        return $result;
    }

    public static function tune($data): array
    {
        $method = explode(',', $data);
        $result = [];
        // PrayerTimes::tune() takes a total of 9 params
        for ($i=0; $i<=8; $i++) {
            if (isset($method[$i]) && $method[$i] != 0 && $method[$i] != null) {
                $result[] = (string) $method[$i];
            } else {
                $result[] = 0;
            }
        }

        return $result;
    }

    /**
     * @param string $timestamp
     * @return bool
     */
    public static function isUnixTimeStamp(string $timestamp): bool
    {

        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
     }

    /**
     * Accepts a date or unix timestamp and returns a unix timestamp
     * @param string $data
     * @return int
     */
    public static function time(string $data): int
    {
        // If it is a timestamp, just return it.
        if (self::isUnixTimeStamp($data)) {
            return $data;
        }

        // Clearly not a timestamp, check if the data is a date format. dd-mm-yyyy
        $date = explode('-', $data);
        if (count($date) == 3) {
            // Cool, we have a date
            $month = self::month($date[1]);
            $year =  self::year($date[2]);
            $timestamp = strtotime(
                self::monthDay($date[0], $month, $year) .
                '-' . $month .
                '-' . $year
            );
            // If we can get a timestamp from the date, return that.
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        // If it is not a unix timestamp not a date we can generate timestamp from, just get the current time!
        return time(); //now
    }

    public static function monthDay($day, $month, $year)
    {
        $maxDaysAllowed = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if ($day > $maxDaysAllowed || $day < 0) {
            // return doday
            return date('j');
        }

        return (int) $day;


    }

    /**
     * @param string $timestamp
     * @param string $timezone
     * @return \DateTime
     */
    public function getDateObjectFromUnixTimestamp(string $timestamp, string $timezone)
    {
        $d = new \DateTime(date('@' . $timestamp));
        if (self::isTimeZoneValid($timezone)) {
            $d->setTimezone(new DateTimeZone($timezone));
        }

        return $d;
    }

    /**
     * [month description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function month($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || ((int) $data) > 12 || ((int) $data) < 1 || strlen($data) > 2 || strlen($data) < 1) {
            $d = new \DateTime('now');
            return $d->format('m');
        }

        return $data;
    }

    /**
     * [hijriMonth description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function hijriMonth($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || $data > 12 || $data < 1) {
            $cs = new HijriCalendarService();
            return $cs->getCurrentIslamicMonth();
        }

        return $data;
    }

    /**
     * [year description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function year($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || strlen($data) < 1 || ((int) $data) < 1 ) {
            $d = new \DateTime('now');
            return $d->format('Y');
        }

        return $data;
    }

    /**
     * [hijriYear description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function hijriYear($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            $cs = new HijriCalendarService();
            return $cs->getCurrentIslamicYear();
        }

        return $data;
    }

    /**
     * [annual description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function annual($data)
    {
        if ($data == 'true') {
            return true;
        }

        return false;
    }

    /**
     * [isLatitudeValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isLatitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }

        return true;
    }

    /**
     * [isLongitudeValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isLongitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }

        return true;
    }

    /**
     * [isTimeZoneValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isTimeZoneValid($data)
    {
        return Generic::isTimeZoneValid($data);
    }

    /**
     * [isTimingsRequestValid description]
     * @param  [type]  $lat      [description]
     * @param  [type]  $lng      [description]
     * @param  [type]  $timezone [description]
     * @return boolean           [description]
     */
    public static function isTimingsRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }

    /**
     * [isCalendarRequestValid description]
     * @param  [type]  $lat      [description]
     * @param  [type]  $lng      [description]
     * @param  [type]  $timezone [description]
     * @return boolean           [description]
     */
    public static function isCalendarRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }

    public static function isValidAddress(string $string): bool
    {
        $characters = ['#', '@', '<', '>', '!', '\\x', '', 'Ä'];
        foreach ($characters as $x) {
            if (strpos($string, $x) !== false) {
                return false;
            }
        }

        if (self::containsEmoji($string)) {
            return false;
        }

        return true;
    }

    public static function containsEmoji($str): bool
    {
        $emoji = Emoji\detect_emoji($str);

        return count($emoji) > 0;
    }

    public static function isValidLocationPair(string $city, string $country): bool
    {
        return self::isValidAddress($city) && self::isValidAddress($country);
    }

}
