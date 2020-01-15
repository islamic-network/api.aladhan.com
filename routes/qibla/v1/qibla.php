<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;

$app->group('/v1', function() {
    $this->get('/qibla/{latitude}/{longitude}', function (Request $request, Response $response) {
        $latitude = floatval($request->getAttribute('latitude'));
        $longitude = floatval($request->getAttribute('longitude'));
        $direction = \AlQibla\Calculation::get($latitude, $longitude);
        $calculation = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'direction' => $direction
        ];

        return $response->withJson(ApiResponse::build($calculation, 200, 'OK'), 200);
    });
});
