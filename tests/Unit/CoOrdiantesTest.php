<?php
use AlAdhanApi\Model\Locations;

class CoOrdiantesTest extends \PHPUnit\Framework\TestCase
{

    public function Setup(): void
    {
        $this->locations = new Locations();
    }

    public function testCoOrdinates()
    {
        $this->assertFalse(\AlAdhanApi\Helper\Generic::isCoOrdinateAValidFormat([0.0]));
        $this->assertFalse(\AlAdhanApi\Helper\Generic::isCoOrdinateAValidFormat(["0.0", "0", null, "null"]));
        $this->assertTrue(\AlAdhanApi\Helper\Generic::isCoOrdinateAValidFormat([0.01212312]));
    }
}
