<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Cacher;
use AlAdhanApi\Helper\Database;

$app->group('/v1', function() {
    $this->get('/status', function (Request $request, Response $response) {
        $mc = new Cacher();
        $dbx = new Database();
        try {
            $db = $dbx->getConnection('database');
            $dbResult = $db->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND countryiso = ?",
                ['Ar-Rayyan', 'QA']);
        } catch (Exception $e) {
            $dbResult = false;
        }
        try {
            $db2 = $dbx->getConnection('database_slave');
            $db2Result = $db2->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND countryiso = ?",
                ['Ar-Rayyan', 'QA']);
            if ($mc !== false) {
                if ($dbResult !== false) {
                    $mc->set('DB_CONNECTION', 'database');
                } else {
                    $mc->set('DB_CONNECTION', 'database_slave');
                }
            }
        } catch (Exception $e) {
            $db2Result = false;
        }
        $status = [
            'memcached' => $mc === false ? 'NOT OK' : 'OK',
            'perconaMaster' => $dbResult === false ? 'NOT OK' : 'OK',
            'perconaSlave' => $db2Result === false ? 'NOT OK' : 'OK',
            'activeDb' => $mc === false ? 'NOT OK' : $mc->get('DB_CONNECTION')
                ];
        if ($mc === false || $dbResult === false || $db2Result === false) {
            return $response->withJson(ApiResponse::build($status, 500, 'Status Check Failed'), 500);
        }
        return $response->withJson(ApiResponse::build($status, 200, 'OK'), 200);
    });
});
