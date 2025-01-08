<?php

namespace Api\Controllers\v1;

use Mamluk\Kipchak\Components\Controllers\Slim;
use Mamluk\Kipchak\Components\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Models\AsmaAlHusna as AAH;


class AsmaAlHusna extends Slim
{

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $aah = new AAH();

        return Http\Response::json($response,
            $aah->get([]),
            200,
            true,
            7200,
            ['public']
        );
    }

    public function getByNumber(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $aah = new AAH();

        $numbers = explode(',', Http\Request::getAttribute($request, 'number'));

        $r = $aah->get($numbers);

        if (empty($r)) {
            return Http\Response::json($response,
                'Please specify a valid number or list of comma separated numbers between 1 and 99',
                404,
                true,
                7200,
                ['public']
            );
        }

        return Http\Response::json($response,
            $r,
            200,
            true,
            7200,
            ['public']
        );
    }

}