<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Helper\Cacher;
use AlAdhanApi\Helper\Database;

$app->group('/v1', function() {
    $this->get('/status', function (Request $request, Response $response) {
        $mc = new Cacher();
        $dbx = new Database();
        $db = $dbx->getConnection('database');
        $dbResult = $db->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND countryiso = ?",
                    ['Dubai', 'AE']);
        $db2 = $dbx->getConnection('database_slave');
        $db2Result = $db2->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND countryiso = ?",
            ['Dubai', 'AE']);

        $status = [
            'memcached' => $mc === false ? 'NOT OK' : 'OK',
            'perconaMaster' => $dbResult === false ? 'NOT OK' : 'OK',
            'perconaSlave' => $db2Result === false ? 'NOT OK' : 'OK'
                ];

        if ($mc === false || $dbResult == false) {
            return $response->withJson(ApiResponse::build($status, 500, 'Status Check Failed'), 500);
        }

        return $response->withJson(ApiResponse::build($status, 200, 'OK'), 200);

    });

});
