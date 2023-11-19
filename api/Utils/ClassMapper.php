<?php

namespace Api\Utils;

use IslamicNetwork\PrayerTimes\Method;
use IslamicNetwork\PrayerTimes\PrayerTimes;

/**
 * Class ClassMapper
 * @package Helper\ClassMapper
 */
class ClassMapper
{
    /**
     * Returns the method name
     * @param  Integer $methodId 0 to 16
     * @return String
     */
    public static function method($methodId)
    {
        $arr = [
            0 => Method::METHOD_JAFARI,
            1 => Method::METHOD_KARACHI,
            2 => Method::METHOD_ISNA,
            3 => Method::METHOD_MWL,
            4 => Method::METHOD_MAKKAH,
            5 => Method::METHOD_EGYPT,
            7 => Method::METHOD_TEHRAN,
            8 => Method::METHOD_GULF,
            9 => Method::METHOD_KUWAIT,
            10 => Method::METHOD_QATAR,
            11 => Method::METHOD_SINGAPORE,
            12 => Method::METHOD_FRANCE,
            13 => Method::METHOD_TURKEY,
            14 => Method::METHOD_RUSSIA,
            15 => Method::METHOD_MOONSIGHTING,
            16 => Method::METHOD_DUBAI,
            17 => Method::METHOD_JAKIM,
            18 => Method::METHOD_TUNISIA,
            19 => Method::METHOD_ALGERIA,
            20 => Method::METHOD_KEMENAG,
            21 => Method::METHOD_MOROCCO,
            99 => Method::METHOD_CUSTOM,
        ];

        if (array_key_exists($methodId, $arr)) {
            return $arr[$methodId];
        }

        return $arr[2]; // ISNA


    }

    /**
     * Returns School name
     * @param  Integer $id
     * @return String
     */
    public static function school($id)
    {
        if ($id == 0) {
            return PrayerTimes::SCHOOL_STANDARD;
        }

        if ($id == 1) {
            return PrayerTimes::SCHOOL_HANAFI;
        };

        return PrayerTimes::SCHOOL_STANDARD;
    }

        /**
     * Returns Midnight mode
     * @param  Integer $id
     * @return String
     */
    public static function midnightMode($id)
    {
        if ($id == 0) {
            return PrayerTimes::MIDNIGHT_MODE_STANDARD;
        }

        if ($id == 1) {
            return PrayerTimes::MIDNIGHT_MODE_JAFARI;
        };

        return PrayerTimes::MIDNIGHT_MODE_STANDARD;
    }

    /**
     * Returns name of Latitude Adjustment Method
     * @param  Integer $id
     * @return String
     */
    public static function latitudeAdjustmentMethod($id)
    {
        if ($id == 1) {
            return PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_MOTN;
        }

        if ($id == 2) {
            return PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_ONESEVENTH;
        }

        if ($id == 3) {
            return PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_ANGLE;
        }

        return PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_NONE;
    }
}
