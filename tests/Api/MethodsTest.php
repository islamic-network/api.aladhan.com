<?php

class MethodsTest extends \PHPUnit\Framework\TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'https://api.aladhan.com/v1/']);
    }

    public function tearDown() {
        $this->http = null;
    }

    public function testMethods()
    {
        $response = $this->http->request('GET', 'methods');
        $this->assertEquals(200, $response->getStatusCode());


        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json;charset=utf-8", $contentType);

        $responseBody = json_decode($response->getBody());
        $this->assertEquals(14, count((array)$responseBody->data));
    }

}
