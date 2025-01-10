<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

/**
 * @var $container ContainerInterface
 */

$container->set('cache.apcu.cache', function(ContainerInterface $c) {
    $cache = new ApcuAdapter(
        $namespace = 'api',
        $defaultLifetime = 3600,
        $version = null
    );

    return $cache;
});
