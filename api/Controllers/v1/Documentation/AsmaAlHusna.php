<?php

namespace Api\Controllers\v1\Documentation;

use Api\Utils\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi as OApi;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class AsmaAlHusna extends Documentation
{
    public MemcachedAdapter $mc;

    public function generate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->mc = $this->container->get('cache.memcached.cache');
        $openApi = $this->mc->get('oa_aaa', function (ItemInterface $item) {
            $item->expiresAfter(300);
            return OApi\Generator::scan([$this->dir . '/Controllers/v1/AsmaAlHusna.php']);
        });

        return Response::raw($response, $openApi->toYaml(), 200, ['Content-Type' => 'text/yaml']);
    }

}