<?php

namespace AlAdhanApi\Helper;

/**
 * Class Response
 * @package Helper\Response
 */
class Response
{
    /**
     * [build description]
     * @param  [type] $data   [description]
     * @param  [type] $code   [description]
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public static function build($data, $code, $status, $json = true)
    {
        if ($json) {
            return json_encode(
                [
                    'code' => $code,
                    'status' => $status,
                    'data' => $data
                ]
            );
        }

        return
            [
                'code' => $code,
                'status' => $status,
                'data' => $data
            ];
    }

    public static function print(\Psr\Http\Message\ResponseInterface $response, $data, $code, $status, $cache = false, $length = 300)
    {
        $r = self::build($data, $code, $status);

        $response->getBody()->write($r);

        if ($cache) {
            return $response
                ->withAddedHeader('Content-Type', 'application/json')
                ->withAddedHeader('Cache-Control', 'public, must-revalidate, max-age=' . $length)
                ->withStatus($code);
        }

        return $response
            ->withAddedHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }
}
