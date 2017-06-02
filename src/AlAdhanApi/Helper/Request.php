<?php

namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Generic;

class Request
{
    
    public static function school($data)
    {
        if (in_array($data, [0, 1])) {
            return $data;
        } else {
            return 0;
        }
    }
    
    public static function latitudeAdjustmentMethod($data)
    {
        if (!in_array($data, [1, 2, 3])) {
            return 3;
        } else {
            return $data;
        }
    }
    
    public static function method($data)
    {
        if (!in_array($data, [1, 2, 3, 4, 5, 7])) {
            return 2; // ISNA
        } else {
            return $data;
        }
    }
    
    public static function time($data)
    {
        if ($data < 1 || !is_numeric($data)) {
            return time();
        } else {
            return $data;
        }
    }
    
    public static function month($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || $data > 12 || $data < 1) {
            $d = new \DateTime('now');
            return $d->format('m');    
        }
        
        return $data;
    }
    
    public static function year($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            $d = new \DateTime('now');
            return $d->format('Y');    
        }
        
        return $data;
    }
    
    public static function annual($data)
    {
        if ($data == 'true') {
            return true;   
        }
        
        return false;
    }
    
    public static function isLatitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }
        
        return true;  
    }
    
    public static function isLongitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }
        
        return true;
    }
    
    
    public static function isTimeZoneValid($data)
    {
        return Generic::isTimeZoneValid($data);
    }
            
    public static function isTimingsRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }
    
    public static function isCalendarRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }
    
}
