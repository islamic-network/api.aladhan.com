<?php
namespace AlAdhanApi\Helper;

use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * [$config description]
     * @var [type]
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
     * [connection description]
     * @param  string $id [description]
     * @return [type]     [description]
     */
    public function connection($id = 'database')
    {
        return (object) $this->config['connections'][$id];
    }

    /**
     * [apiKey description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function apiKey($id)
    {
        return $this->config['apikeys'][$id];
    }
}
