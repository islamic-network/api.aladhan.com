<?php
namespace AlAdhanApi\Handler;


use IslamicNetwork\Waf\Exceptions\BlackListException;
use IslamicNetwork\Waf\Exceptions\RateLimitException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AlAdhanHandler
{
    public function __invoke($request, $response, $exception = null) {

        if ($exception instanceof BlackListException) {
            return $response->withJson(self::blacklist(), 403);
        }

        if ($exception instanceof RateLimitException) {
            return $response->withJson(self::ratelimit(), 429);
        }


        $r = [
            'code' => 500,
            'status' => 'Internal Server Error',
            'data' => 'Something went wrong when the server tried to process this request. Sorry!'
        ];

        return $response->withJson($r, 500);
    }

    public function blacklist(): array
    {
        return [
            'code' => 403,
            'status' => 'Forbidden',
            'data' => 'You are on the Blacklist. If you think this is an error, please contact us via the information on https://aladhan.com/contact.'
        ];
    }

    public function ratelimit(): array
    {
        return [
            'code' => 429,
            'status' => 'Too Many Requests',
            'data' => 'You have been rate limited temporarily. If you think this is an error or your use is genuine, please contact us via the information on https://aladhan.com/contact.'
        ];
    }
}