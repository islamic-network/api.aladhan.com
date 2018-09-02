<?php
use AlAdhanApi\Helper\Request;

class TimestampTest extends \PHPUnit\Framework\TestCase
{


    public function testUnixTimestamps()
    {

        $this->assertTrue(Request::isUnixTimeStamp('1'));
        $this->assertFalse(Request::isUnixTimeStamp('1.0'));
        $this->assertFalse(Request::isUnixTimeStamp('1.1'));
        $this->assertFalse(Request::isUnixTimeStamp('0xFF'));
        $this->assertFalse(Request::isUnixTimeStamp('01090'));
        $this->assertTrue(Request::isUnixTimeStamp(0123));
        $this->assertTrue(Request::isUnixTimeStamp('-1000000'));
        $this->assertTrue(Request::isUnixTimeStamp(-1000000));
        $this->assertTrue(Request::isUnixTimeStamp(+1000000));
        $this->assertFalse(Request::isUnixTimeStamp('+1000000'));
        $ts = strtotime('+1 day');
        $this->assertTrue(Request::isUnixTimeStamp($ts));

    }

}
