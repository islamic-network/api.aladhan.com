<?php

class SmokeTest extends \PHPUnit\Framework\TestCase
{
    private $http;

    public function setUp(): void
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://localhost/v1/']);
    }

    public function tearDown(): void
    {
        $this->http = null;
    }

    public function testAsmaAlHusna()
    {
        $response = $this->http->request('GET', 'asmaAlHusna');
        $this->assertEquals(200, $response->getStatusCode());


        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testgToH()
    {
        $response = $this->http->request('GET', 'gToH');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testgToHCalendar()
    {
        $response = $this->http->request('GET', 'gToHCalendar/9/1444');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testhToG()
    {
        $response = $this->http->request('GET', 'hToG');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testhToGCalendar()
    {
        $response = $this->http->request('GET', 'hToGCalendar/12/2021');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testOtherHijris()
    {
        $response = $this->http->request('GET', 'nextHijriHoliday');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'currentIslamicYear');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'currentIslamicMonth');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'islamicYearFromGregorianForRamadan/2011');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'hijriHolidays/27/7');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'specialDays');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'islamicMonths');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'islamicHolidaysByHijriYear/1443');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

    }

    public function testQibla()
    {
        $response = $this->http->request('GET', 'qibla/2.1212/11.213');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testDateAndTime()
    {
        $response = $this->http->request('GET', 'currentTime?zone=Asia/Dubai');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'currentDate?zone=Asia/Dubai');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'currentTimestamp?zone=Asia/Dubai');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testNextPrayerByAddress()
    {
        $response = $this->http->request('GET', 'nextPrayerByAddress?address=Wafi City, Dubai, UAE&iso8601=true');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'nextPrayerByAddress/' . time() . '?address=Wafi City, Dubai, UAE');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testMethods()
    {
        $response = $this->http->request('GET', 'methods');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testTimings()
    {
        $response = $this->http->request('GET', 'timings/22-02-2021?latitude=51.508515&longitude=-0.1254872&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timings/1398332113?latitude=51.508515&longitude=-0.1254872&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timings?latitude=51.508515&longitude=-0.1254872&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testTimingsByCity()
    {
        $response = $this->http->request('GET', 'timingsByCity/22-02-2021?city=London&country=UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timingsByCity/1398332113?city=London&country=UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timingsByCity?city=London&country=UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testTimingsByAddress()
    {
        $response = $this->http->request('GET', 'timingsByAddress/22-02-2021?address=London, UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timingsByAddress/1398332113?address=London, UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'timingsByAddress?address=London, UK&method=2');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testInfo()
    {
        $response = $this->http->request('GET', 'cityInfo?city=London&country=UK');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'addressInfo?address=London, UK');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

    }

    public function testCalendar()
    {
        $response = $this->http->request('GET', 'calendar?latitude=51.508515&longitude=-0.1254872&method=2&month=4&year=2017');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'hijriCalendar?latitude=51.508515&longitude=-0.1254872&method=2&month=4&year=1437');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testCalendarByAddress()
    {
        $response = $this->http->request('GET', 'calendarByAddress?address=Sultanahmet Mosque, Istanbul, Turkey&method=2&month=04&year=2017');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'hijriCalendarByAddress?address=Sultanahmet Mosque, Istanbul, Turkey&method=2&month=04&year=1437');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }

    public function testCalendarByCity()
    {
        $response = $this->http->request('GET', 'calendarByCity?city=London&country=United Kingdom&method=2&month=04&year=2017');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $response = $this->http->request('GET', 'hijriCalendarByCity?city=London&country=United Kingdom&method=2&month=04&year=1437');
        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);
    }
}
