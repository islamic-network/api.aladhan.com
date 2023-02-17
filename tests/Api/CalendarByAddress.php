<?php

class CalendarByAddressTest extends \PHPUnit\Framework\TestCase
{
    private $http;

    public function setUp(): void
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://localhost/v1/']);
    }

    public function tearDown(): void {
        $this->http = null;
    }

    public function testStandardMidnightMode()
    {
        $response = $this->http->request('GET', 'calendarByAddress', [
            'query' => [
                'address' => 'Dubai, UAE',
                'month' => '12',
                'year' => '1973'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals("05:20 (+04)", $responseBody->data[1]->timings->Fajr);
        $this->assertEquals("18:59 (+04)", $responseBody->data[1]->timings->Isha);
        $this->assertEquals("00:08 (+04)", $responseBody->data[1]->timings->Midnight);
        $this->assertEquals("STANDARD", $responseBody->data[1]->meta->midnightMode);
    }

    public function testJafariMidnightMode()
    {
        $response = $this->http->request('GET', 'calendarByAddress', [
            
            'query' => [
                'address' => 'Dubai, UAE',
                'month' => '12',
                'year' => '1973',
                'midnightMode' => 1,
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals("05:20 (+04)", $responseBody->data[1]->timings->Fajr);
        $this->assertEquals("18:59 (+04)", $responseBody->data[1]->timings->Isha);
        $this->assertEquals("23:24 (+04)", $responseBody->data[1]->timings->Midnight);
        $this->assertEquals("JAFARI", $responseBody->data[1]->meta->midnightMode);
    }

}
