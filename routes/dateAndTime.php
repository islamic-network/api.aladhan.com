<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\Generic;


$app->get('/currentTime', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('H:i'), 200, 'OK'), 200);
    }

});

$app->get('/currentDate', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('d-m-Y'), 200, 'OK'), 200);
    }

});

$app->get('/currentTimestamp', function (Request $request, Response $response) {
    $zone = $request->getQueryParam('zone');
    if ($zone == '' || $zone == null || !Generic::isTimeZoneValid($zone)) {
        return $response->withJson(ApiResponse::build('Please specify a valid timezone. Example: Europe/London', 400, 'Bad Request'), 400);
    } else {
        $date = new DateTime('now', new DateTimeZone($zone));
        return $response->withJson(ApiResponse::build($date->format('U'), 200, 'OK'), 200);
    }
});
