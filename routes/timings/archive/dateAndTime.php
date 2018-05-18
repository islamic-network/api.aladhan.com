<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\Generic;

/**
 * @api {get} http://api.aladhan.com/currentTime Current Time
 * @apiDescription Returns the current time (in the HH:MM format) for the specified time zone
 * @apiName GetCurrentTime
 * @apiGroup DateAndTime
 * @apiVersion 1.0.1
 *
 * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
 *
 * @apiExample {http} Example usage:
 *   http://api.aladhan.com/currentTime?zone=Europe/London
  *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "code": 200,
 *   "status": "OK",
 *   "data": "13:56"
 * }
 */
$app->get('/currentTime', function (Request $request, Response $response) {
    //$this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('H:i'), 200, 'OK'), 200);
    }

});

/**
 * @api {get} http://api.aladhan.com/currentDate Current Date
 * @apiDescription Returns the current date (in the DD-MM-YYYY format) for the specified time zone
 * @apiName GetCurrentDate
 * @apiGroup DateAndTime
 * @apiVersion 1.0.1
 *
 * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
 *
 * @apiExample {http} Example usage:
 *   http://api.aladhan.com/currentDate?zone=Europe/London
  *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "code": 200,
 *   "status": "OK",
 *   "data": "23-08-2017"
 * }
 */
$app->get('/currentDate', function (Request $request, Response $response) {
    //$this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('d-m-Y'), 200, 'OK'), 200);
    }

});

/**
 * @api {get} http://api.aladhan.com/currentTimestamp Current Timestamp
 * @apiDescription Returns the current UNIX timestamp for the specified time zone
 * @apiName GetCurrentTimestamp
 * @apiGroup DateAndTime
 * @apiVersion 1.0.1
 *
 * @apiParam {string} zone  A valid timezone name as specified on <a href="http://php.net/manual/en/timezones.php" target="_blank">http://php.net/manual/en/timezones.php</a><. Example: Europe/London
 *
 * @apiExample {http} Example usage:
 *   http://api.aladhan.com/currentTimestamp?zone=Europe/London
  *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "code": 200,
 *   "status": "OK",
 *   "data": "1503495668"
 * }
 */
$app->get('/currentTimestamp', function (Request $request, Response $response) {
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('U'), 200, 'OK'), 200);
    }
});
