<?php
namespace AlAdhanApi\Handler;

use AlAdhanApi\Exception\WafKeyMismatchException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class AlAdhanHandler
{
    public function __invoke($request, $response, $exception = null) {

        if ($exception instanceof WafKeyMismatchException) {
            $r = [
                'code' => 403,
                'status' => 'Forbidden',
                'data' => 'WAF Key Mismatch.'
            ];

            return $response->withJson($r, 403);
        };

        $r = [
            'code' => 500,
            'status' => 'Internal Server Error',
            'data' => 'Something went wrong when the server tried to process this request. Sorry!'
        ];

        return $response->withJson($r, 500);
    }

}
