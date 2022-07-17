<?php


namespace AlAdhanApi\Interceptor;
use AlAdhanApi\Helper\Generic;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class CoOrdinates
{
    public static function areValid(ServerRequestInterface $request, $latitude, $longitude)
    {
        if (!Generic::isCoOrdinateAValidFormat([$latitude, $longitude])) {
            throw new HttpBadRequestException($request,'The geographical coordinates you specified (latitude and longitude) are invalid.');
        }
    }

}