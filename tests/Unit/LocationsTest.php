<?php
use AlAdhanApi\Model\Locations;

class LocationsTest extends \PHPUnit\Framework\TestCase
{
    private $client;

    public function Setup(): void
    {
        $this->locations = new Locations();
    }

    public function testGoogleCity()
    {
        $this->markTestSkipped('Tests google, not our code so much.');
        $r = $this->locations->getGoogleCoOrdinatesAndZone('Inverness', 'UK', 'Scotland');
        $this->assertEquals('57.477773', $r['latitude']);
        $this->assertEquals('-4.224721', $r['longitude']);
        $this->assertEquals('Europe/London', $r['timezone']);
    }

    public function testGoogleAddress()
    {
        $this->markTestSkipped('Tests google, not our code so much.');
        $r = $this->locations->getAddressCoOrdinatesAndZone('Inverness, Scotland, UK');
        $this->assertEquals('57.477773', $r['latitude']);
        $this->assertEquals('-4.224721', $r['longitude']);
        $this->assertEquals('Europe/London', $r['timezone']);
    }
}
