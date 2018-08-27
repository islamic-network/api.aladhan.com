<?php

namespace AlAdhanApi\Handler;


class AlAdhanRateLimitHandler
{

    public function __invoke($request, $response, $exception = null) {
        $r = [
            'code' => 429,
            'status' => 'Too Many Requests',
            'data' => 'You have been rate limited temporarily. If you think this is an error or your use is genuine, please contact us via the information on https://aladhan.com/contact.'
        ];

        return $response->withJson($r, 429);
    }

}