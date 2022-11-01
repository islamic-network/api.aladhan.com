<?php

use AlAdhanApi\Helper\Request;

class RequestHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testYear()
    {
        $this->assertEquals('1754', Request::year('1754'));
        $this->assertEquals(date('Y'), Request::year('-1'));
        $this->assertEquals(date('Y'), Request::year('abc'));
        $this->assertEquals('2', Request::year('2'));
        $this->assertEquals('13', Request::year('13'));
        $this->assertEquals('777', Request::year('777'));
    }

    public function testMonth()
    {
        $this->assertEquals(date('m'), Request::month('15'));
        $this->assertEquals(date('m'), Request::month('-1'));
        $this->assertEquals(date('m'), Request::month('0'));
        $this->assertEquals('2', Request::month('2'));
        $this->assertEquals(date('m'), Request::month('abc'));
    }

    public function testMonthDay()
    {
        $this->assertEquals(3, Request::monthDay(3, 12, 2017));
        $this->assertEquals(date('j'), Request::monthDay(-1, 12, 2017));
        $this->assertEquals(date('j'), Request::monthDay(55, 12, 2017));
    }

    public function testTime()
    {
        $this->assertEquals(strtotime('11-11-2017'), Request::time('11-11-2017'));
        $time = time() + 25200;
        $this->assertEquals($time, Request::time($time));
    }

    public function testClosestMethodTest()
    {
        // Dubai, UAE
        $this->assertEquals(8, Request::calculateClosestMethod(25.002074, 55.168764));

        // Paris, France
        $this->assertEquals(12, Request::calculateClosestMethod(48.8566, 2.3522));

        // Dallas, Texas
        $this->assertEquals(2, Request::calculateClosestMethod(32.7766, -96.7969));
    }

}
