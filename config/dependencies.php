<?php
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Model\Locations;
use AlAdhanApi\Handler\AlAdhanHandler;
use AlAdhanApi\Handler\AlAdhanNotFoundHandler;
use Symfony\Component\Yaml\Yaml;
use IslamicNetwork\Waf\Exceptions\BlackListException;
use IslamicNetwork\Waf\Exceptions\RateLimitException;
use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;

$container = $app->getContainer();

$container['helper'] = function($c) {
    $helper = new \stdClass();
    $helper->logger = new Log();

    return $helper;
};

$container['model'] = function($c) {
    $model = new \stdClass();
    $model->locations = new Locations($c['helper']->logger);

    return $model;
};

$container['notFoundHandler'] = function ($c) {
    return new AlAdhanNotFoundHandler();
};

$container['errorHandler'] = function ($c) {
    return new AlAdhanHandler();
};

/** Invoke Middleware for WAF Checks */
$app->add(function ($request, $response, $next) {

    $wafRules = new RuleSet(realpath(__DIR__ . '/waf.yml'));
    $waf = new RuleSetMatcher($wafRules, $request->getHeaders(), []);
    if ($waf->isBlacklisted()) {
        throw new BlackListException();
    } elseif ($waf->isRatelimited()) {
        $mc = new \AlAdhanApi\Helper\Cacher();
        $matched = $waf->getMatched();
        $rl = new \IslamicNetwork\Waf\Model\RateLimit($mc, $matched['name'], $matched['limit'], $matched['time']);
        if ($rl->isLimited()) {
            throw new RateLimitException();
        } else {
            $response = $next($request, $response);
        }
    } else {
        $response = $next($request, $response);
    }

    return $response;
});
