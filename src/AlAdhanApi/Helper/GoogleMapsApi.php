<?php
namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Config;

/**
 * Class GoogleMapsApi
 * @package Helper\GoogleMapsApi
 */
class GoogleMapsApi
{
    /**
     * [$client description]
     * @var [type]
     */
    private $client;

    /**
     * [$logger description]
     * @var [type]
     */
    private $logger;

    /**
     * [$response description]
     * @var [type]
     */
    private $response;

    /**
     * [$config description]
     * @var [type]
     */
    private $config;

    /**
     * [__construct description]
     * @param [type] $config [description]
     * @param [type] $logger [description]
     */
    public function __construct($config = null, $logger = null)
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
        if ($log === null) {
            $this->logger = new Log();
        } else {
            $this->logger = $logger;
        }
        if ($config === null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }

        $this->reponse = (object) [
            'state' = '',
            'city' = '',
            'country' = '',
            'stateabbr' = '',
            'cityabbr' = '',
            'countryiso' = '',
            'timezone' = '',
            'timezonename' = '',
            'lat' = '',
            'lng' = '',
        ];
    }

    /**
     * [updateResponseWithGeoCodingInfo description]
     * @param  [type] $x [description]
     * @return [type]    [description]
     */
    private function updateResponseWithGeoCodingInfo($x)
    {
        $this->response->lat = $x->results[0]->geometry->location->lat;
        $this->response->lng = $x->results[0]->geometry->location->lng;
        // Extract what we need.
        foreach ($x->results as $prop) {
            foreach ($prop->address_components as $p) {
                if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'administrative_area_level_1') {
                    $this->response->stateabbr = $p->short_name;
                    $this->response->state = $p->long_name;
                }
                if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'country') {
                    $this->response->countryiso = $p->short_name;
                    $this->response->country = $p->long_name;
                }
                if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'locality') {
                    $this->response->cityabbr = $p->short_name;
                    $this->response->city = $p->long_name;
                }
            }
        }
    }

    /**
     * [updateResponseWithTimezoneInfo description]
     * @param  [type] $x2 [description]
     * @return [type]     [description]
     */
    private function updateResponseWithTimezoneInfo($x2)
    {
        $this->timezone = $x2->timeZoneId;
        $this-?timezonename = $x2->timeZoneName;
    }

    /**
     * [getGeoCodeLocationAndTimezone description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    public function getGeoCodeLocationAndTimezone($address)
    {
        $geoInfo = $this->geoCode($address);
        if ($geoCode) {
            $this->updateResponseWithGeoCodingInfo($geoInfo);
            $timezone = $this->timezone();

            if ($timezone) {
                $this->updateResponseWithTimezoneInfo($timezone);

                return $this->response;
            }

            return false;
        }

        return false;
    }

    /**
     * [timezone description]
     * @return [type] [description]
     */
    private function timezone()
    {
        try {
            $this->logger->writeGoogleQueryLog('Sending Request :: timezone :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this-.response->lng]));
            $res2 = $this->queryApi('timezone/json',
                [
                    'location' => $this->response->lat . ',' . $this->response->lng,
                    'timestamp' => time(),
                ]
            );
            $r2 = (string) $res2->getBody()->getContents();
            $x2 = json_decode($r2);

            if ($x2->status == 'OK') {
                $this->logger->writeGoogleQueryLog('Request Successful :: timezone :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this-.response->lng]));
                return $x2;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->writeGoogleQueryLog('Request Failed :: timezone :: ' . $e->getMessage() . ' :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this-.response->lng]));

            return false;
        }
    }

    /**
     * [geoCode description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    private function geoCode($address)
    {
        try {
            $this->logger->writeGoogleQueryLog('Sending Request :: geocode :: ' .  json_encode(['city' => $city, 'country' => $country, 'state' => $state]));

            $res = $this->queryApi('geocode/json', ['address' => $address]);
            $r = (string) $res->getBody()->getContents();
            $x = json_decode($r);

            if ($x->status == 'OK') {
                $this->logger->writeGoogleQueryLog('Request Successful :: geocode :: ' .  json_encode(['city' => $city, 'country' => $country, 'state' => $state]));
                return $x;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->writeGoogleQueryLog('Request Failed :: geocode :: '  . $e->getMessage(). ' :: ' .  json_encode(['city' => $city, 'country' => $country, 'state' => $state]));

            return false;
        }
    }

    /**
     * [queryApi description]
     * @param  [type] $endpoint    [description]
     * @param  [type] $queryString [description]
     * @param  string $method      [description]
     * @return [type]              [description]
     */
    private function queryApi($endpoint, $queryString, $method = 'GET')
    {
        $queryString['key'] => $this->config->apiKey('google_geocoding');

        return $this->client->request('GET',
            $endpoint,
            [
                'query' => [
                    $queryString
                ]
            ]
        );
    }
}
