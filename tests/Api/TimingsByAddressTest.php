<?php

class TimingsByAddressTest extends \PHPUnit\Framework\TestCase
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

    public function testStandardMidnightMode()
    {
        // Without the date
        $response = $this->http->request('GET', 'timingsByAddress', [
            'query' => [
                'address' => 'Dubai, UAE',
            ]
        ]);

        // This will ensure the 301 is followed
        $this->assertEquals(200, $response->getStatusCode());

        // With a date (should also add one with a UNIX timestamp)
        $response = $this->http->request('GET', 'timingsByAddress/02-12-1973', [
            'query' => [
                'address' => 'Dubai, UAE',
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals("05:20", $responseBody->data->timings->Fajr);
        $this->assertEquals("18:59", $responseBody->data->timings->Isha);
        $this->assertEquals("00:08", $responseBody->data->timings->Midnight);
        $this->assertEquals("STANDARD", $responseBody->data->meta->midnightMode);
    }

    public function testJafariMidnightMode()
    {
        $response = $this->http->request('GET', 'timingsByAddress/02-12-1973', [
            'query' => [
                'address' => 'Dubai, UAE',
                'midnightMode' => 1,
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals("05:20", $responseBody->data->timings->Fajr);
        $this->assertEquals("18:59", $responseBody->data->timings->Isha);
        $this->assertEquals("23:24", $responseBody->data->timings->Midnight);
        $this->assertEquals("JAFARI", $responseBody->data->meta->midnightMode);
    }

}
