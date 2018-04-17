<?php

namespace AlAdhanApi\Helper;

/**
 * Class ClassMapper
 * @package Helper\ClassMapper
 */
class ClassMapper
{
    /**
     * Returns the method name
     * @param  Integer $methodId 0 to 12
     * @return String
     */
    public static function method($methodId)
    {
        $arr = [
            0 => 'JAFARI',
            1 => 'KARACHI',
            2 => 'ISNA',
            3 => 'MWL',
            4 => 'MAKKAH',
            5 => 'EGYPT',
            7 => 'TEHRAN',
            8 => 'GULF',
            9 => 'KUWAIT',
            10 => 'QATAR',
            11 => 'SINGAPORE',
            12 => 'FRANCE',
            13 => 'TURKEY',
            99 => 'CUSTOM',
        ];

        return $arr[$methodId];
    }

    /**
     * Returns School name
     * @param  Integer $id
     * @return String
     */
    public static function school($id)
    {
        if ($id == 0) {
            return 'STANDARD';
        }

        if ($id == 1) {
            return 'HANAFI';
        };

        return 'STANDARD';
    }

        /**
     * Returns Midnight mode
     * @param  Integer $id
     * @return String
     */
    public static function midnightMode($id)
    {
        if ($id == 0) {
            return 'STANDARD';
        }

        if ($id == 1) {
            return 'JAFARI';
        };

        return 'STANDARD';
    }

    /**
     * Returns name of Latitude Adjustment Method
     * @param  Integer $id
     * @return String
     */
    public static function latitudeAdjustmentMethod($id)
    {
        if ($id == 1) {
            return 'MIDDLE_OF_THE_NIGHT';
        }

        if ($id == 2) {
            return 'ONE_SEVENTH';
        }

        if ($id == 3) {
            return 'ANGLE_BASED';
        }

        return 'NONE';
    }
}
