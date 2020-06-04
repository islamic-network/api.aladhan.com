<?php


namespace AlAdhanApi\Interceptor;
use AlAdhanApi\Helper\Generic;

class CoOrdinates
{
    public static function areValid($latitude, $longitude)
    {
        if (!Generic::isCoOrdinateAValidFormat([$latitude, $longitude])) {
            throw new \AlAdhanApi\Exception\BadRequestException('The geographical coordinates you specified (latitude and longitude) are invalid.', 400);
        }
    }

}