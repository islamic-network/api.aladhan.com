<?php
use AlAdhanApi\Helper\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testDatabase()
    {
        $c = new Config();
        $this->assertEquals(5, count((array)$c->connection('database')));
    }

    public function testMemcache()
    {
        $c = new Config();
        $this->assertEquals(2, count((array)$c->connection('memcache')));
    }

    public function testGoogleKey()
    {
        $c = new Config();
        $this->assertTrue(is_string($c->apiKey('google_geocoding')), 'Actual Value: ' . $c->apiKey());
    }
}
