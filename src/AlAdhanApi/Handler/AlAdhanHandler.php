<?php
namespace AlAdhanApi\Handler;

use AlAdhanApi\Exception\BadRequestException;
use AlAdhanApi\Helper\Log;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

class AlAdhanHandler
{
    public function __invoke(Request $request, Response $response, Exception $exception) {

        if ($exception instanceof BadRequestException) {
            $r = [
                'code' => $exception->getCode(),
                'status' => 'Bad Request',
                'data' => $exception->getMessage()
            ];

            return $response->withJson($r, 400);
        };

        $log = new Log();
        $errorJson = json_encode(
            [
                'code' => @$exception->getCode(),
                'message' => @$exception->getMessage(),
                'trace' => @$exception->getTraceAsString()
            ]
        );

        $log->error('AlAdhan Exception Triggered: ' . $errorJson);

        return $response->withJson($r, $exception->getCode());
    }

}
