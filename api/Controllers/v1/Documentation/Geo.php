<?php

namespace Api\Controllers\v1\Documentation;

use Api\Utils\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi as OApi;
class Geo extends Documentation
{
    public function generate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $openApi = OApi\Generator::scan(
            [
                $this->dir . '/Controllers/v1/Geo.php'
            ]
        );

        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'text/yaml']);
    }
}