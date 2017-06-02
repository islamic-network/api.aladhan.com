<?php
namespace AlAdhanApi\Helper;
use AlAdhanApi\Helper\Config;

class Cacher
{
    /**
     * [$mc description]
     * @var [type]
     */
    private $mc;


    /**
     * [__construct description]
     */
    public function __construct($host = null, $port = null)
    {
        $appConfig = new Config();
        $config = $appConfig->connection('memcache');

        if ($host === null) {
            $host = $config->host;
        }

        if ($port === null) {
            $port = $config->port;
        }

        $this->mc = new \Memcached();

        try {
            $this->mc->addServer($host, $port);
        } catch (Exception $e) {
            return false;
        }

    }

    public function generateKey($id, array $params)
    {
        return $id . ':' . implode('_', str_replace(' ', '', $params));
    }

    /**
     * [set description]
     * @param [type] $k [description]
     * @param [type] $v [description]
     */
    public function set($k, $v)
    {
        return $this->mc->set($k, $v);
    }

    public function get($k)
    {
        return $this->mc->get($k);
    }
}
