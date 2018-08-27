<?php
namespace AlAdhanApi\Handler;


class AlAdhanNotFoundHandler
{
    public function __invoke($request, $response, $exception = null) {
        $r = [
            'code' => 404,
            'status' => 'Not Found',
            'data' => 'Invalid endpoint or resource.'
        ];

        return $response->withJson($r, 404);
    }
}