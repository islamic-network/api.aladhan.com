<?php
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Model\Locations;
use AlAdhanApi\Handler\AlAdhanHandler;
use AlAdhanApi\Handler\AlAdhanNotFoundHandler;
use IslamicNetwork\Waf\Exceptions\BlackListException;
use IslamicNetwork\Waf\Exceptions\RateLimitException;
use IslamicNetwork\Waf\Model\RuleSet;
use IslamicNetwork\Waf\Model\RuleSetMatcher;
use IslamicNetwork\Waf\Model\RateLimit;

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

/*$container['notFoundHandler'] = function ($c) {
    return new AlAdhanNotFoundHandler();
};

$container['errorHandler'] = function ($c) {
    return new AlAdhanHandler();
};*/

/** Invoke Middleware for WAF Checks */
$app->add(function ($request, $response, $next) {
    $log = new Log();
    $server = [];

    if (isset($_SERVER)) {
        $server = $_SERVER;
    }

    $wafRules = new RuleSet(realpath(__DIR__ . '/waf.yml'));
    $waf = new RuleSetMatcher($wafRules, $request->getHeaders(), $server);
    if ($waf->isWhitelisted()) {
        // $log->writeWAFLog('WHITELISTED');
        $response = $next($request, $response);
    } elseif ($waf->isBlacklisted()) {
        $log->writeWAFLog('BLACKLISTED');
        throw new BlackListException();
    } elseif ($waf->isRatelimited()) {
        $mc = new \AlAdhanApi\Helper\Cacher();
        $matched = $waf->getMatched();
        $log->writeWAFLog('RATELIMIT MATCHED :: ' . $matched['name']);
        $rl = new RateLimit($mc, $matched['name'], $matched['rate'], $matched['time']);
        if ($rl->isLimited()) {
            $log->writeWAFLog('RATELIMITED :: ' . $matched['name']);
            throw new RateLimitException();
        }
    } else {
        $response = $next($request, $response);
    }

    return $response;
});
