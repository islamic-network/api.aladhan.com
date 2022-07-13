<?php

class MethodsTest extends \PHPUnit\Framework\TestCase
{
    private $http;

    public function setUp(): void
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'http://localhost:8080/v1/']);
    }

    public function tearDown(): void
    {
        $this->http = null;
    }

    public function testMethods()
    {
        $response = $this->http->request('GET', 'methods');
        $this->assertEquals(200, $response->getStatusCode());


        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals(16, count((array)$responseBody->data));
    }

}
