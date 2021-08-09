<?php
namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Config;
use AlAdhanApi\Helper\AskGeo;

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
        if ($logger === null) {
            $this->logger = new Log();
        } else {
            $this->logger = $logger;
        }
        if ($config === null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }

        $this->askGeo = new AskGeo($this->config, $this->logger);

        $this->response = (object) [
            'state' => '',
            'city' => '',
            'country' => '',
            'stateabbr' => '',
            'cityabbr' => '',
            'countryiso' => '',
            'timezone' => '',
            'timezonename' => '',
            'lat' => '',
            'lng' => '',
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
        $this->response->timezone = $x2->timeZoneId;
        $this->response->timezonename = $x2->timeZoneName;
    }

    /**
     * [getGeoCodeLocationAndTimezone description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    public function getGeoCodeLocationAndTimezone($address)
    {
        $geoInfo = $this->geoCode($address);
        if ($geoInfo) {
            $this->updateResponseWithGeoCodingInfo($geoInfo);
            $tz = $this->timezone();

            // Artifically adding timeZoneName below. It is part of the response and stored in one table but is not used for anything, so we 
            // don't really need it to be pretty descriptive name, per se.
            $timezone = (object) ['timeZoneId' => $tz, 'timeZoneName' => $tz];

            if ($timezone) {
                $this->updateResponseWithTimezoneInfo($timezone);

                return $this->response;
            }

            return false;
        }

        return false;
    }

    public function getTimezoneByCoOrdinates($lat, $lng)
    {
        $r = $this->timezone($lat, $lng);

        if (isset($r->timeZoneId)) {
            return $r->timeZoneId;
        }

        return false;

    }

    /**
     * [timezone description]
     * @return [type] [description]
     */
    private function timezone($lat = null, $lng = null)
    {
        if ($lat !== null && $lng !== null) {
            $this->response->lat = $lat;
            $this->response->lng = $lng;
        }

        // Move all this to ask geo as google is expensive
        return $this->askGeo->getTimezoneByCoOrdinates($this->response->lat, $this->response->lng);
    }

    /**
     * [geoCode description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    private function geoCode($address)
    {
        try {
            $startTime = microtime(true);
            $this->logger->writeGoogleQueryLog('Sending Request :: geocode :: ' .  json_encode(['city_state_country' => $address]));

            $res = $this->queryApi('geocode/json', ['address' => $address]);
            $r = (string) $res->getBody()->getContents();
            $endTime = microtime(true);
            $x = json_decode($r);
            $responseTime = $endTime - $startTime;
            if ($x->status == 'OK') {
                $this->logger->writeGoogleQueryLog('Request Successful :: geocode :: ' .  json_encode(['city_state_country' => $address, 'response_time' => $responseTime]));
                return $x;
            }

            $this->logger->writeGoogleQueryLog('Request Unsuccessful :: geocode :: ' .  json_encode(['city_state_country' => $address, 'result' => $x, 'response_time' => $responseTime]));

            return false;
        } catch (Exception $e) {
            $this->logger->writeGoogleQueryLog('Request Failed :: geocode :: '  . $e->getMessage(). ' :: ' .  json_encode(['city_state_country' => $address]));

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
        $queryString['key'] = $this->config->apiKey('google_geocoding');

        return $this->client->request('GET',
            $endpoint,
            [
                'query' => $queryString,
                'connect_timeout' => 3,
                'read_timeout' => 3
            ]
        );
    }
}
