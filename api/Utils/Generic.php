<?php

namespace Api\Utils;

class Generic
{

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
                strpos($_SERVER['HTTP_USER_AGENT'], 'YandexBot') !== false ||
                strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false
            )
        ) {
            return true;
        }

        return false;
    }

    public static function isCoOrdinateAValidFormat(array $coordinates)
    {
        $invalidValues = [
            "0.0", "0", "null", null, 0.0, 0
        ];

        foreach ($coordinates as $coordinate) {
            if (in_array($coordinate, $invalidValues)) {
                return false;
            }
        }

        return true;
    }
}
