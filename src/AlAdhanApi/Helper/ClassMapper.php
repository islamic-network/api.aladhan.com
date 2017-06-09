<?php

namespace AlAdhanApi\Helper;

class ClassMapper
{
    
    public static function method($methodId)
    {
        $arr = [
            0 => 'JAFARI',
            1 => 'KARACHI',
            2 => 'ISNA',
            3 => 'MWL',
            4 => 'MAKKAH',
            5 => 'EGYPT',
            7 => 'TEHRAN'
        ];
        
        return $arr[$methodId];
    }
    
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
