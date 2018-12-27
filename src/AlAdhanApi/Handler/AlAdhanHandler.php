<?php
namespace AlAdhanApi\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlAdhanHandler
{
    public function __invoke($request, $response, $exception = null) {

        $r = [
            'code' => 500,
            'status' => 'Internal Server Error',
            'data' => 'Something went wrong when the server tried to process this request. Sorry!'
        ];

        return $response->withJson($r, 500);
    }

}