<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Generic;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    /**
     * @api {get} http://api.aladhan.com/v1/currentTime Current Time
     * @apiDescription Returns the current time (in the HH:MM format) for the specified time zone
     * @apiName GetCurrentTime
     * @apiGroup DateAndTime
     * @apiVersion 1.0.1
     *
     * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/currentTime?zone=Europe/London
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "status": "OK",
     *   "data": "13:56"
     * }
     */
    $group->map(['GET', 'OPTIONS'], '/currentTime', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $zone = isset($request->getQueryParams()['zone']) ? $request->getQueryParams()['zone'] : '';
        if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
            return ApiResponse::print($response, 'Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request');
        } else {
            $date = new DateTime('now', new DateTimeZone($zone));
            return ApiResponse::print($response, $date->format('H:i'), 200, 'OK');
        }

    });

    /**
     * @api {get} http://api.aladhan.com/v1/currentDate Current Date
     * @apiDescription Returns the current date (in the DD-MM-YYYY format) for the specified time zone
     * @apiName GetCurrentDate
     * @apiGroup DateAndTime
     * @apiVersion 1.0.1
     *
     * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/currentDate?zone=Europe/London
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "status": "OK",
     *   "data": "23-08-2017"
     * }
     */
    $group->map(['GET', 'OPTIONS'], '/currentDate', function (Request $request, Response $response) {
        //$this->helper->logger->write();
        $zone = isset($request->getQueryParams()['zone']) ? $request->getQueryParams()['zone'] : '';
        if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
            return ApiResponse::print($response, 'Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request');
        } else {
            $date = new DateTime('now', new DateTimeZone($zone));
            return ApiResponse::print($response, $date->format('d-m-Y'), 200, 'OK');
        }

    });

    /**
     * @api {get} http://api.aladhan.com/v1/currentTimestamp Current Timestamp
     * @apiDescription Returns the current UNIX timestamp for the specified time zone
     * @apiName GetCurrentTimestamp
     * @apiGroup DateAndTime
     * @apiVersion 1.0.1
     *
     * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/currentTimestamp?zone=Europe/London
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "status": "OK",
     *   "data": "1503495668"
     * }
     */
    $group->map(['GET', 'OPTIONS'], '/currentTimestamp', function (Request $request, Response $response) {
        $zone = isset($request->getQueryParams()['zone']) ? $request->getQueryParams()['zone'] : '';
        if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
            return ApiResponse::print($response, 'Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request');
        } else {
            $date = new DateTime('now', new DateTimeZone($zone));
            return ApiResponse::print($response, $date->format('U'), 200, 'OK');
        }
    });
});
