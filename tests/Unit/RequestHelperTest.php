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

}