<?php

namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Generic;

/**
 * Class Request
 * @package Helper\Request
 */
class Request
{
    /**
     * [school description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function school($data)
    {
        if (in_array($data, [0, 1])) {
            return $data;
        } else {
            return 0;
        }
    }

    /**
     * [latitudeAdjustmentMethod description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function latitudeAdjustmentMethod($data)
    {
        if (!in_array($data, [1, 2, 3])) {
            return 3;
        } else {
            return $data;
        }
    }

    /**
     * [method description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function method($data)
    {
        if (!in_array($data, [1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 99])) {
            return 2; // ISNA
        } else {
            return $data;
        }
    }

    public static function customMethod($data)
    {
        $method = explode(',', $data);
        $result = [];
        // PrayerTimesHelper::createCustomMethod() takes a total of 12 params
        for ($i=0; $i<=11; $i++) {
            if (isset($method[$i]) && $method[$i] != 0 && $method[$i] != null) {
                $result[] = $method[$i];
            } else {
                $result[] = null;
            }
        }

        return $result;
    }

    /**
     * [time description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function time($data)
    {
        if ($data < 1 || !is_numeric($data)) {
            return time();
        } else {
            return $data;
        }
    }

    /**
     * [month description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function month($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || $data > 12 || $data < 1) {
            $d = new \DateTime('now');
            return $d->format('m');
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
        if ($data == '' || $data == null || !is_numeric($data)) {
            $d = new \DateTime('now');
            return $d->format('Y');
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
}
