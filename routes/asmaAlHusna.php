<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use AlAdhanApi\Helper\Response as ApiResponse;
use AlAdhanApi\Helper\Request as ApiRequest;
use AlAdhanApi\Model\AsmaAlHusna;


$app->get('/asmaAlHusna', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $names = AsmaAlHusna::get();

    return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

});

$app->get('/asmaAlHusna/{no}', function (Request $request, Response $response) {
    $this->helper->logger->write();
    $number = $request->getAttribute('no');
    $number = explode(',', $number);
    $nos = [];
    foreach ($number as $no) {
        $nos[] = (int) $no;
    }
    $names = AsmaAlHusna::get($nos);

    if ($names == false) {
        return $response->withJson(ApiResponse::build('Please specify a valid number between 1 and 99', 400, 'Bad Request'), 400);
    }

    return $response->withJson(ApiResponse::build($names, 200, 'OK'), 200);

});
