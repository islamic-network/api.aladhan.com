<?php
namespace AlAdhanApi\Helper;

use AlAdhanApi\Helper\Config;
use AlAdhanApi\Helper\Cacher;
use AlAdhanApi\Helper\Generic;
use AlAdhanApi\Helper\Log;

/**
 * Class Database
 * @package Helper\Database
 */

class Database
{
    private $config;
    private $db;
    private $cacher;
    private $logger;

    // Constants mapped to methods in DB class.
    const ID_DB_CoOrdinatesAndTimezone = 1;
    const ID_DB_GoogleCoOrdinatesAndZone = 2;
    const ID_DB_checkGeolocateTable = 3;
    const ID_DB_checkIfGeoRecordExistsViaCo = 4;
    const ID_DB_checkQuery = 5;
    const ID_DB_checkAddressQuery = 6;
    const ID_DB_checkInvalidQuery = 7;
    const ID_DB_getAddressCoOrdinatesAndZone = 8;

    /**
     * Constructor
     */
    public funcion __construct($logger = null)
    {
        $this->config = new Config();
        $this->cacher = new Cacher();
        if ($log === null) {
            $this->logger = new Log();
        } else {
            $log = $log;
        }

        $this->db = $this->getConnection();
    }

    /**
     * Returns a connection to the database
     * @return DriveManager
     */
    public function getConnection()
    {
        $config = new \Doctrine\DBAL\Configuration();

        $c = $this->config->connection('database');

        $connectionParams = array(
            'dbname' => $c->dbname,
            'user' => $c->username,
            'password' => $c->password,
            'host' => $c->host,
            'driver' => 'pdo_mysql',
        );

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Array          [description]
     */
    public function getCoOrdinatesAndTimezone($city, $country, $state = '')
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_CoOrdinatesAndTimezone, [$city, $country, $state]);
        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        if ($state == '') {

            $sql = 'SELECT city.latitude, city.longitude, city.timezone FROM city
                    LEFT JOIN country
                    ON country.iso = city.countryiso
                    WHERE
                    (LCASE(country.printable_name) = ? OR LCASE(country.iso) = ?)
                    AND LCASE(city.name) = ?';

            $stmnt = $this->db->executeQuery($sql, [strtolower($country), strtolower($country), strtolower($city)]);

            $result = $stmnt->fetch();
        } else {
            $sql = 'SELECT city.latitude, city.longitude, city.timezone FROM city
                LEFT JOIN country
                ON country.iso = city.countryiso
                LEFT JOIN state
                ON country.iso = state.countryiso
                WHERE
                (LCASE(country.printable_name) = ? OR LCASE(country.iso) = ?)
                AND
                (LCASE(state.name) = ? OR LCASE(state.abbreviation) = ?)
                AND
                LCASE(city.name) = ?';

            $stmnt = $this->db->executeQuery($sql, [strtolower($country), strtolower($country), strtolower($state), strtolower($state), strtolower($city)]);

            $result = $stmnt->fetch();
        }
        $this->cacher->set($cacheKey, $result);

        return $result;

    }

    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Array          [description]
     */
    public function getGoogleCoOrdinatesAndZone($city, $country, $state = '')
    {
        $cityO = $city;
        $countryO = $country;
        $stateO = $state;

        if (!$this->citySanitizer($city, $country)) {
            return false;
        }


        $cacheKey = $this->cacher->generateKey(self::ID_DB_GoogleCoOrdinatesAndZone, [$city, $country, $state]);
        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $checkQuery = $this->checkQuery($cityO, $countryO, $stateO);

        if ($checkQuery) {
            return $checkQuery;
        }

        $local = $this->checkGeolocateTable($city, $country, $state);

        if ($local) {
            return $local;
        }

        $string = $city;
        if ($state != '') {
            $string .= ', ' . $state;
        }
        $string .= ', ' . $country;

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);

        $this->logger->writeGoogleQueryLog('Sending Request :: geocode :: ' .  json_encode(['city' => $city, 'country' => $country, 'state' => $state]));

        $res = $client->request('GET',
                         'geocode/json',
                         [
                             'query' => [
                                 'address' => $string,
                                 'key' => $this->config->apiKey('google_geocoding')
                             ]
                         ]
                        );
        $r = (string) $res->getBody()->getContents();
        $x = json_decode($r);

        $state = '';
        $city = '';
        $country = '';
        $stateabbr = '';
        $cityabbr = '';
        $countryiso = '';
        $timezone = '';
        $timezonename = '';

        if ($x->status == 'OK') {
            $lat = $x->results[0]->geometry->location->lat;
            $lng = $x->results[0]->geometry->location->lng;
            // Extract what we need.
            foreach ($x->results as $prop) {
                //var_dump($prop);
                foreach ($prop->address_components as $p){
                    if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'administrative_area_level_1') {
                        $stateabbr = $p->short_name;
                        $state = $p->long_name;
                    }
                    if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'country') {
                        $countryiso = $p->short_name;
                        $country = $p->long_name;
                    }
                    if (is_array($p->types) && $p->types[0] !== null && $p->types[0] == 'locality') {
                        $cityabbr = $p->short_name;
                        $city = $p->long_name;
                    }
                }
            }
            // Get timezone
            $this->logger->writeGoogleQueryLog('Sending Request :: timezone :: ' . json_encode(['lat' => $lat, 'lng' => $lng]));
                $res2 = $client->request('GET',
                         'timezone/json',
                         [
                             'query' => [
                                 'location' => $lat . ',' . $lng,
                                 'timestamp' => time(),
                                 'key' => $this->config->apiKey('google_geocoding')
                             ]
                         ]
                        );
                $r2 = (string) $res2->getBody()->getContents();
                $x2 = json_decode($r2);

                if ($x2->status == 'OK') {
                    $timezone = $x2->timeZoneId;
                    $timezonename = $x2->timeZoneName;


                    // It may be that the user entered an unconventional format above, but if already have the latitue and longitude, don't re-create the record. We want 1 entry for each combination of co-ordinates.
                    if (!$this->checkIfGeoRecordExistsViaCo($city, $country, $state)) {
                        // Write update database
                        $insert = $this->db->insert('geolocate',
                                         [
                                             'countryiso' => $countryiso,
                                             'country' => $country,
                                             'state' => $state,
                                             'stateabbr' => $stateabbr,
                                             'city' => $city,
                                             'cityabbr' => $cityabbr,
                                             'latitude' => $lat,
                                             'longitude' => $lng,
                                             'timezone' => $timezone,
                                             'timezonename' => $timezonename
                                         ]
                                         );
                        }

                    $this->recordQuery($cityO, $stateO, $countryO, $lat, $lng, $timezone);

                    $result = [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'timezone' => $timezone
                    ];

                    $this->cacher->set($cacheKey, $result);

                    return $result;
                }

            return false;
        }

        return false;
    }


    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Array          [description]
     */
    public static function checkGeolocateTable($city, $country, $state)
    {

        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkGeolocateTable, [$city, $country, $state]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }


        if ($state == '') {
            $result = $this->db->fetchAssoc("SELECT latitude, longitude, timezone
                                FROM geolocate WHERE
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                )",
            [strtolower($country), strtolower($country),strtolower($city), strtolower($city)]);
        } else {
            $result = $this->db->fetchAssoc("SELECT latitude, longitude, timezone
                                FROM geolocate WHERE
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                AND
                                (LCASE(state) = ? OR LCASE(stateabbr) = ?)",
            [strtolower($country), strtolower($country), strtolower($city), strtolower($city), strtolower($state), strtolower($state)]);
        }

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Array          [description]
     */
    private function checkIfGeoRecordExistsViaCo($city, $country, $state) {

        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkIfGeoRecordExistsViaCo, [$city, $country, $state]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $result = $this->db->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND country = ? AND state = ?",
            [$city, $country, $state]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Array          [description]
     */
    public function citySanitizer($city, $country, $state = '')
    {
        $city = strtolower($city);
        $country = strtolower($country);

        if ($city == '' || $country == '' || $city == null || $city == 'null' || $country == 'strcountrycode') {
            return false;
        }

        if ($city == 'ramallah' && $country == 'ps') {
            return false;
        }

        if ($city == 'london' && $country == 'sa') {
            return false;
        }

        return true;
    }

        public static function recordQuery($city, $state, $country, $lat, $lng, $timezone)
    {

        return $this->db->insert('geolocate_queries',
                                         [
                                             'city' => $city,
                                             'state' => $state,
                                             'country' => $country,
                                             'latitude' => $lat,
                                             'longitude' => $lng,
                                             'timezone' => $timezone,
                                         ]
                                         );

    }

    /**
     * @param  String $address    [description]
     * @return
     */
    public static function recordInvalidQuery($address)
    {
        return $this->db->insert('address_geolocate_invalid', ['query' => $address]);
    }

    /**
     * @param  String $city    [description]
     * @param  String $country [description]
     * @param  String $state   [description]
     * @return Mixed          [description]
     */
    public function checkQuery($city, $country, $state)
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkQuery, [$city, $country, $state]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        if ($state == '') {
            $result = $this->db->fetchAssoc(
                "SELECT latitude, longitude, timezone
                FROM geolocate_queries WHERE
                (LCASE(country) = ?)
                AND
                (LCASE(city) = ?)
                ",
                [$country, $city]);
        } else {
            $result = $this->db->fetchAssoc(
                "SELECT latitude, longitude, timezone
                FROM geolocate_queries WHERE
                (LCASE(country) = ?)
                AND
                (LCASE(city) = ?)
                AND
                (LCASE(state) = ?)
                ",
                [$country, $city, $state]);
        }

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    /**
     * @param  String $address    [description]
     * @return Mixed          [description]
     */
    public function checkAddressQuery($address)
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkAddressQuery, [$address]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $result = $this->db->fetchAssoc(
                "SELECT latitude, longitude, timezone
                FROM address_geolocate_queries WHERE
                (LCASE(address) = ?)
                ",
                [strtolower($address)]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    /**
     * @param  String $address    [description]
     * @return Mixed          [description]
     */
    public checkInvalidQuery($address) {

        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkInvalidQuery, [$address]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $result = $this->db->fetchAssoc(
                "SELECT id
                FROM address_geolocate_invalid WHERE
                (LCASE(query) = ?)
                ",
                [strtolower($address)]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    /**
     * @param  String $address    [description]
     * @return Mixed          [description]
     */
    public static function getAddressCoOrdinatesAndZone($address)
    {
        if ($address == '' || $address == null) {
            return false;
        }
        $address = (string) $address;


        $cacheKey = $this->cacher->generateKey(self::ID_DB_getAddressCoOrdinatesAndZone, [$address]);

        if ($this->cacher->check($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $checkAddress = $this->checkAddressQuery($address);

        if ($checkAddress)
        {
            return $checkAddress;
        }

        $checkInvalidQuery = $this->checkInvalidQuery($address);

        if ($checkInvalidQuery) {
            return false;
        }

        // If Google Bot is querying, return false or it's a self propelling cycle of Google making money!
        if (Generic::isGoogleBot()) {
            return false;
        }

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);

        $this->logger->writeGoogleQueryLog('Sending Request :: geocode :: ' . json_encode(['address' => $address, $_REQUEST]) . ' ::: ');
        // Geocoding call.
        $res = $client->request('GET',
                         'geocode/json',
                         [
                             'query' => [
                                 'address' => $address,
                                 'key' => $this->config->apiKey('google_geocoding')
                             ]
                         ]
                        );
        $r = (string) $res->getBody()->getContents();
        $x = json_decode($r);

        if ($x->status == 'OK') {
            $lat = $x->results[0]->geometry->location->lat;
            $lng = $x->results[0]->geometry->location->lng;

            // Timezone call.
            $this->logger->writeGoogleQueryLog('Sending Request :: timezone :: ' . json_encode(['lat' => $lat, 'lng' => $lng, $_REQUEST]) . ' ::: ' );
            $res2 = $client->request('GET',
                         'timezone/json',
                         [
                             'query' => [
                                 'location' => $lat . ',' . $lng,
                                 'timestamp' => time(),
                                 'key' => $this->config->apiKey('google_geocoding')
                             ]
                         ]
                        );
                $r2 = (string) $res2->getBody()->getContents();
                $x2 = json_decode($r2);

                if ($x2->status == 'OK') {
                    $timezone = $x2->timeZoneId;

                    // Update datbase

                    $insert = $this->db->insert('address_geolocate_queries',
                                         [
                                             'address' => $address,
                                             'latitude' => $lat,
                                             'longitude' => $lng,
                                             'timezone' => $timezone
                                         ]
                                         );
                    $result = [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'timezone' => $timezone
                    ];

                    $this->cacher->set($cacheKey, $result);

                    return $result;
            }
        } else {
            $this->recordInvalidQuery($address);

            return false;
        }
    }
}
