<?php
namespace AlAdhanApi\Helper;

use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @package Helper\Config
 */
class Config
{
    /**
     * The Parsyed Yaml config file
     * @var Object
     */
    private $config;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $configFilePath = realpath(__DIR__ . '/../../../config/') . '/config.yml';
        $this->config = Yaml::parse(file_get_contents($configFilePath));
    }

    /**
     * Gets a specific connection type, for example database or memcached
     * @param  String $id Defined in the config.yml file
     * @return Object
     */
    public function connection($id = 'database')
    {
        return (object) $this->config['connections'][$id];
    }

    /**
     * Returns a particular Api key in the Yaml file
     * @param  String $id
     * @return Mixed (most likely string)
     */
    public function apiKey($id)
    {
        return $this->config['apikeys'][$id];
    }
    
    /**
     * Returns the entire config array
     * @return Array The entire config array
     */
    public function getConfig()
    {
        return $this->config;
    }


}
