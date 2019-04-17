<?php
namespace AlAdhanApi\Handler;

use AlAdhanApi\Exception\WafKeyMismatchException;
use AlAdhanApi\Helper\Log;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

class AlAdhanHandler
{
    public function __invoke(Request $request, Response $response, Exception $exception) {

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

        $log = new Log();
        $errorJson = json_encode(
            [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]
        );

        $log->error('AlAdhan Exception Triggered: ' . $errorJson);

        return $response->withJson($r, $exception->getCode());
    }

}
