<?php
namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Config;
use AlAdhanApi\Helper\Cacher;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\GoogleMapsApi;

/**
 * Class Database
 * @package Helper\Database
 */

class Database
{
    private $config;
    private $cacher;
    private $google;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = new Config();
        $this->cacher = new Cacher();
    }

    /**
     * Returns a connection to the database
     * @return DriveManager
     */
    public function getConnection($connection = 'database')
    {
        $config = new \Doctrine\DBAL\Configuration();

        if ($this->cacher !== false &&
            in_array($this->cacher->get('DB_CONNECTION'), ['database', 'database_slave'])) {
            $connection = $this->cacher->get('DB_CONNECTION');
        }

        $c = $this->config->connection($connection);

        $connectionParams = array(
            'dbname' => $c->dbname,
            'user' => $c->username,
            'password' => $c->password,
            'host' => $c->host,
            'driver' => 'pdo_mysql',
        );

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }


}
