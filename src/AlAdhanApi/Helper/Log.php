<?php
namespace AlAdhanApi\Helper;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class Log
 * @package AlAdhanApi\Helper
 */

class Log
{
    public $id;
    public $directory;

    public function __construct($directory = null)
    {
        $this->id = uniqid();
        if ($directory === null) {
            $this->directory = realpath(__DIR__ . '/../../../logs/') . '/';
        } else {
            $this->directory = $directory;
        }
    }
    /**
     * Returns a formatted log array§
     * @param  Array $server  $_SERVER
     * @param  Array $request $_REQUEST
     * @return Array
     */
    public function format($server, $request)
    {
        $l = [];
        // Request Params
        $l['request'] = $request;
        $l['server'] = [
            'ip' => $server['REMOTE_ADDR'],
            'url' => isset($server['SCRIPT_URL']) ? $server['SCRIPT_URL'] : $server['REDIRECT_URL'],
            'method' => $server['REQUEST_METHOD']
        ];
        if (isset($server['HTTP_USER_AGENT'])) {
            $l['server']['useragent'] = $server['HTTP_USER_AGENT'];
        }
        if (isset($server['HTTP_ORIGIN'])) {
            $l['server']['origin'] = $server['HTTP_ORIGIN'];
        }
        if (isset($server['HTTP_REFERER'])) {
            $l['server']['referer'] = $server['HTTP_REFERER'];
        }

        return $l;
    }

    /**
     * Writes a Long Entry for the Google Maps API
     * @param String $message
     */
    public function writeGoogleQueryLog($message)
    {
        $logFile = 'Google_' . date('Y-m-d');
        // Create the logger
        $logger = new Logger('GoogleEndpoint');
        // Now add some handlers
        $logger->pushHandler(new StreamHandler($this->directory . $logFile . '.log', Logger::INFO));

        return $logger->addInfo($this->id . $message . json_encode([$referer, $agent, $_SERVER['QUERY_STRING'], $this->format($_SERVER, $_REQUEST)]));
    }

    public function write()
    {
        $logFile = date('Y-m-d');
        $logger = new Logger('ApiService');
        // Now add some handlers
        $logger->pushHandler(new StreamHandler($this->directory . $logFile . '.log', Logger::INFO));
        
        return $logger->addInfo($this->id . 'Incoming request :: ', $this->format($_SERVER, $_REQUEST));
    }
}
