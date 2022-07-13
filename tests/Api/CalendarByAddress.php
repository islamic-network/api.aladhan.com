<?php

class CalendarByAddressTest extends \PHPUnit\Framework\TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://localhost:8080/v1/']);
    }

    public function tearDown() {
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
        $this->assertEquals("05:20 (GST)", $responseBody->data[1]->timings->Fajr);
        $this->assertEquals("18:59 (GST)", $responseBody->data[1]->timings->Isha);
        $this->assertEquals("00:08 (GST)", $responseBody->data[1]->timings->Midnight);
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
        $this->assertEquals("05:20 (GST)", $responseBody->data[1]->timings->Fajr);
        $this->assertEquals("18:59 (GST)", $responseBody->data[1]->timings->Isha);
        $this->assertEquals("23:24 (GST)", $responseBody->data[1]->timings->Midnight);
        $this->assertEquals("JAFARI", $responseBody->data[1]->meta->midnightMode);
    }

}
