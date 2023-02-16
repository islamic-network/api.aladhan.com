<?php

use Api\Controllers;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    /**
     * @api {get} http://api.aladhan.com/v1/methods Prayer Times Methods
     * @apiDescription  Returns all the prayer times calculation methods supported by this API. For more information on how to use custom methods, see <a href="https://aladhan.com/calculation-methods" target="_blank">https://aladhan.com/calculation-methods</a>.
     * @apiName GetMethods
     * @apiGroup Miscellaneous
     * @apiVersion 1.0.1
     *
     * @apiExample {http} Example usage:
     *   http://api.aladhan.com/v1/methods
     *
     * @apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     * {
     *     "code": 200,
     *     "status": "OK",
     *     "data": {
     *         "MWL": {
     *             "id": 3,
     *             "name": "Muslim World League",
     *             "params": {
     *                 "Fajr": 18,
     *                 "Isha": 17
     *             }
     *         },
     *         "ISNA": {
     *             "id": 2,
     *             "name": "Islamic Society of North America (ISNA)",
     *             "params": {
     *                 "Fajr": 15,
     *                 "Isha": 15
     *             }
     *         },
     *         .... More methods
     *         "CUSTOM": {
     *             "id": 99
     *         }
     *     }
     * }
     *
     **/
    $group->map(['GET', 'OPTIONS'],'/methods', [Controllers\v1\Methods::class, 'get']);

});