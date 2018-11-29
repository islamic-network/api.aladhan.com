<?php
use AlAdhanApi\Model\HijriCalendarService;

class HijriTest extends \PHPUnit\Framework\TestCase
{
    private $hcs;

    public function setup()
    {
        $this->hcs = new HijriCalendarService();
    }

    public function testGToH()
    {
        $r = $this->hcs->gToH("14-07-2018");
        $this->assertEquals('01-11-1439', $r['hijri']['date']);
        $r = $this->hcs->gToH("14-07-2018", 1);
        $this->assertEquals('02-11-1439', $r['hijri']['date']);
        $r = $this->hcs->gToH("14-07-2018", -1);
        $this->assertEquals('30-10-1439', $r['hijri']['date']);
    }

    public function testHToG()
    {
        $r = $this->hcs->hToG("01-11-1439");
        $this->assertEquals('01-11-1439', $r['hijri']['date']);
        $r = $this->hcs->hToG("01-11-1439", 1);
        $this->assertEquals('02-11-1439', $r['hijri']['date']);
        $r = $this->hcs->hToG("01-11-1439", -1);
        $this->assertEquals('30-10-1439', $r['hijri']['date']);
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
        $this->assertEquals('16-02-1439', $r);
        $r = $this->hcs->adjustHijriDate("29-02-1439", 2);
        $this->assertEquals('01-03-1439', $r);
        $r = $this->hcs->adjustHijriDate("29-12-1439", 2);
        $this->assertEquals('01-01-1440', $r);
        $r = $this->hcs->adjustHijriDate("29-12-1439", -2);
        $this->assertEquals('27-12-1439', $r);
        $r = $this->hcs->adjustHijriDate("01-01-1440", -2);
        $this->assertEquals('29-12-1439', $r);

    }

    public function testDateContruct()
    {
        $this->hcs->ConstractDayMonthYear('25-10-2018', 'DD-MM-YYYY');
        $this->assertEquals('25', $this->hcs->Day);
        $this->assertEquals('10', $this->hcs->Month);
        $this->assertEquals('2018', $this->hcs->Year);

        $this->hcs->ConstractDayMonthYear('25102018', 'DDMMYYYY');
        $this->assertEquals('25', $this->hcs->Day);
        $this->assertEquals('10', $this->hcs->Month);
        $this->assertEquals('2018', $this->hcs->Year);
    }

    public function testIslamicHolidaysByHijriYear()
    {
        $holydays = $this->hcs->getIslamicHolidaysByHijriYear(1440, -1);
        $this->assertCount(19, $holydays);
        //print_r($holydays);
        $this->assertEquals('Ashura', $holydays[0]['hijri']['holidays'][0]);
    }
}


