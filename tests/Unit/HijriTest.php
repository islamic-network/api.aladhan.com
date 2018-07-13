<?php
use AlAdhanApi\Model\HijriCalendarService;

class HijriTest extends \PHPUnit\Framework\TestCase
{
    private $hcs;

    public function setup()
    {
        $this->hcs = new HijriCalendarService();
    }

    public function testGregorianDateAdjustment()
    {
        $r = $this->hcs->adjustGregorianDate("16-12-2018", 1);
        $this->assertEquals('17-12-2018', $r);
        $r = $this->hcs->adjustGregorianDate("16-12-2018", 2);
        $this->assertEquals('18-12-2018', $r);
        $r = $this->hcs->adjustGregorianDate("16-12-2018", 7);
        $this->assertEquals('23-12-2018', $r);
        $r = $this->hcs->adjustGregorianDate("30-12-2018", 7);
        $this->assertEquals('06-01-2019', $r);
    }

    public function testHijriDateAdjustment()
    {
        $r = $this->hcs->adjustHijriDate("14-02-1439", 2);
        $this->assertEquals('16-2-1439', $r);
        $r = $this->hcs->adjustHijriDate("29-02-1439", 2);
        $this->assertEquals('1-3-1439', $r);
        $r = $this->hcs->adjustHijriDate("29-12-1439", 2);
        $this->assertEquals('1-1-1440', $r);
        $r = $this->hcs->adjustHijriDate("29-12-1439", -2);
        $this->assertEquals('27-12-1439', $r);
        $r = $this->hcs->adjustHijriDate("01-01-1440", -2);
        $this->assertEquals('29-12-1439', $r);
        
    }
}
