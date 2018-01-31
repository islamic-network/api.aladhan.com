<?php
namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Log;
use AlAdhanApi\Helper\Config;
use Meezaan\AskGeo\AskGeoAPI;

/**
 * Class GoogleMapsApi
 * @package Helper\GoogleMapsApi
 */
class AskGeo
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
        if ($config === null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }

        $this->config = $this->config->apiKey('askgeo');

        $this->client = new AskGeoAPI($this->config['accountid'], $this->config['key'], $this->config['format'], $this->config['secure']);
        if ($logger === null) {
            $this->logger = new Log();
        } else {
            $this->logger = $logger;
        }

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


    public function getTimezoneByCoOrdinates($lat, $lng)
    {
        $r = $this->timezone($lat, $lng);

        if (isset($r->TimeZoneId)) {
            return $r->TimeZoneId;
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
        try {
            $this->logger->writeAskGeoQueryLog('Sending Request :: timezone :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this->response->lng]));
            $res2 = $this->client->getTimeZone([$this->response->lat, $this->response->lng]);
            $x2 = $res2->data[0];
            if ($res2->message == 'ok') {
                $this->logger->writeAskGeoQueryLog('Request Successful :: timezone :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this->response->lng]));
                return $x2->TimeZone;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->writeAskGeoQueryLog('Request Failed :: timezone :: ' . $e->getMessage() . ' :: ' . json_encode(['lat' => $this->response->lat, 'lng' => $this->response->lng]));

            return false;
        }
    }

}
