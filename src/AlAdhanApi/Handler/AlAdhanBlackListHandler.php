<?php

namespace AlAdhanApi\Handler;


class AlAdhanBlackListHandler
{

    public function __invoke($request, $response, $exception = null) {
        $r = [
            'code' => 403,
            'status' => 'Forbidden',
            'data' => 'You are on the Blacklist. If you think this is an error, please contact us via the information on https://aladhan.com/contact.'
        ];

        return $response->withJson($r, 403);
    }

}