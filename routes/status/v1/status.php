<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Cacher;
use AlAdhanApi\Helper\Database;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1', function(RouteCollectorProxy $group) {
    $group->get('/status', function (Request $request, Response $response) {
        $mc = new Cacher();
        $dbx = new Database();
        $dbResult = false;
        try {
            $db = $dbx->getConnection('database');
            $dbResult = $db->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND countryiso = ? LIMIT 1",
                ['Dubai', 'AE']);    
        } catch (Exception $e) {
            $dbResult = false;
        }

        $status = [
            'memcached' => $mc === false ? 'NOT OK' : 'OK',
            'database' => $dbResult === false ? 'NOT OK' : 'OK (' . $dbResult['id']. ')'
        ];
        if ($mc === false || $dbResult === false) {
            return ApiResponse::print($response, $status, 500, 'Status Check Failed');
        }
        return ApiResponse::print($response, $status, 200, 'OK');

    });

    $group->get('/liveness', function (Request $request, Response $response) {
        return ApiResponse::print($response, 'OK',200, 'OK');
    });
});
