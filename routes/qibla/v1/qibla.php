<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;

$app->group('/v1', function() {
    /**
     * @api {get} http://api.aladhan.com/v1/qibla/:latitude/:longitude Request the Qibla direction from a pair of given co-ordinates
     * @apiName qibla
     * @apiDescription Request the Qibla direction from a pair of co-ordinates
     * @apiGroup Qibla
     * @apiVersion 1.0.0
     *
     * @apiParam {float} latitude A float latitude value. Example: 25.4106386
     * @apiParam {float} longitude A float longitude value.. Example: 51.1846025
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/qibla/25.4106386/51.1846025
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     code: 200,
     *     status: "OK",
     *     data: {
     *      latitude: 25.4106386,
     *      longitude: -54.189238,
     *      direction: 68.92406695044804,
     *     }
     * }
     */
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
