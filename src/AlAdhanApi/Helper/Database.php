<?php
namespace AlAdhanApi\Helper;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AlAdhanApi\Helper\Config;
use AlAdhanApi\Helper\Cacher;

/**
 * Class Database
 * @package Database\Helper
 */

class Database
{

    private $appConfig;
    private $cacher;

    // Constants mapped to methods in DB class.
    const ID_DB_CoOrdinatesAndTimezone = 1;
    const ID_DB_GoogleCoOrdinatesAndZone = 2;
    const ID_DB_checkGeolocateTable = 3;
    const ID_DB_checkIfGeoRecordExistsViaCo = 4;
    const ID_DB_checkQuery = 5;
    const ID_DB_checkAddressQuery = 6;
    const ID_DB_checkInvalidQuery = 7;
    const ID_DB_getAddressCoOrdinatesAndZone = 8;

    public static function writeLog($message) {
        $logId = uniqid();
        $logStamp = time();
        $logFile = 'Google_' . date('Y-m-d', $logStamp);
        $logTime = $logId . ' :: ' . date('Y-m-d H:i:s :: ');
        // Create the logger
        $logger = new Logger('GoogleEndpoint');
        // Now add some handlers
        $logger->pushHandler(new StreamHandler(realpath(__DIR__ . '/../../../logs/') . '/' .$logFile . '.log', Logger::INFO));
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            $referer = '';
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = '';
        }
	    $uri = isset($_SERVER['SCRIPT_URI']) ? $_SERVER['SERVER_URI'] : $_SERVER['REDIRECT_URL'];
        $logger->addInfo($message . json_encode([$referer, $agent, $_SERVER['QUERY_STRING'], $uri]));
        $this->appConfig = new Config();
        $this->cacher = new Cacher();

    }

    public static function isGoogleBot()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') !== false) {
            return true;
        }

        return false;

    }


    public static function getConnection()
    {
        $config = new \Doctrine\DBAL\Configuration();

        $c = $this->appConfig->connection('database')

        $connectionParams = array(
            'dbname' => $c->dbname,
            'user' => $c->username,
            'password' => $c->password,
            'host' => $c->host,
            'driver' => 'pdo_mysql',
        );

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public static function getCoOrdinatesAndTimezone($city, $country, $state = '')
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_CoOrdinatesAndTimezone, [$city, $country, $state]);
        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $db = self::getConnection();

        if ($state == '') {

            $sql = 'SELECT city.latitude, city.longitude, city.timezone FROM city
                    LEFT JOIN country
                    ON country.iso = city.countryiso
                    WHERE
                    (LCASE(country.printable_name) = ? OR LCASE(country.iso) = ?)
                    AND LCASE(city.name) = ?';

            $stmnt = $db->executeQuery($sql, [strtolower($country), strtolower($country), strtolower($city)]);

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

            $stmnt = $db->executeQuery($sql, [strtolower($country), strtolower($country), strtolower($state), strtolower($state), strtolower($city)]);

            $result = $stmnt->fetch();
        }
        $this->cacher->set($cacheKey, $result);

        return $result;

    }

    public static function getGoogleCoOrdinatesAndZone($city, $country, $state = '')
    {
        $cityO = $city;
        $countryO = $country;
        $stateO = $state;

        if (!self::citySanitizer($city, $country)) {
            return false;
        }

        $cacheKey = $this->cacher->generateKey(self::ID_DB_GoogleCoOrdinatesAndZone, [$city, $country, $state]);
        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $checkQuery = self::checkQuery($cityO, $countryO, $stateO);

        if ($checkQuery) {
            return $checkQuery;
        }

        $local = self::checkGeolocateTable($city, $country, $state);

        if ($local) {
            return $local;
        }

        $string = $city;
        if ($state != '') {
            $string .= ', ' . $state;
        }
        $string .= ', ' . $country;

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);

        self::writeLog('Sending Request :: geocode :: ' .  json_encode(['city' => $city, 'country' => $country, 'state' => $state]));

        $res = $client->request('GET',
                         'geocode/json',
                         [
                             'query' => [
                                 'address' => $string,
                                 'key' => $this->appConfig->apiKey('google_geocoding')
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
            self::writeLog('Sending Request :: timezone :: ' . json_encode(['lat' => $lat, 'lng' => $lng]));
                $res2 = $client->request('GET',
                         'timezone/json',
                         [
                             'query' => [
                                 'location' => $lat . ',' . $lng,
                                 'timestamp' => time(),
                                 'key' => $this->appConfig->apiKey('google_geocoding')
                             ]
                         ]
                        );
                $r2 = (string) $res2->getBody()->getContents();
                $x2 = json_decode($r2);

                if ($x2->status == 'OK') {
                    $timezone = $x2->timeZoneId;
                    $timezonename = $x2->timeZoneName;

                    $db = self::getConnection();
                    // It may be that the user entered an unconventional format above, but if already have the latitue and longitude, don't re-create the record. We want 1 entry for each combination of co-ordinates.
                    if (!self::checkIfGeoRecordExistsViaCo($city, $country, $state)) {
                        // Write update database
                        $insert = $db->insert('geolocate',
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

                    self::recordQuery($cityO, $stateO, $countryO, $lat, $lng, $timezone);

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

    public static function checkGeolocateTable($city, $country, $state)
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkGeolocateTable, [$city, $country, $state]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $db = self::getConnection();
        if ($state == '') {
            $result = $db->fetchAssoc("SELECT latitude, longitude, timezone
                                FROM geolocate WHERE
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                )",
            [strtolower($country), strtolower($country),strtolower($city), strtolower($city)]);
        } else {
            $result = $db->fetchAssoc("SELECT latitude, longitude, timezone
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

    private static function checkIfGeoRecordExistsViaCo($city, $country, $state) {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkIfGeoRecordExistsViaCo, [$city, $country, $state]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }
        $db = self::getConnection();
        $result = $db->fetchAssoc("SELECT id
                                FROM geolocate WHERE
                                city = ? AND country = ? AND state = ?",
            [$city, $country, $state]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    public static function citySanitizer($city, $country, $state = '')
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
        $db = self::getConnection();
        return $db->insert('geolocate_queries',
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

    public static function recordInvalidQuery($address)
    {
        $db = self::getConnection();

        return $db->insert('address_geolocate_invalid', ['query' => $address]);
    }

    public static function checkQuery($city, $country, $state)
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkQuery, [$city, $country, $state]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $db = self::getConnection();
        if ($state == '') {
            $result = $db->fetchAssoc(
                "SELECT latitude, longitude, timezone
                FROM geolocate_queries WHERE
                (LCASE(country) = ?)
                AND
                (LCASE(city) = ?)
                ",
                [$country, $city]);
        } else {
            $result = $db->fetchAssoc(
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

    public static function checkAddressQuery($address)
    {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkAddressQuery, [$address]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $db = self::getConnection();

        $result = $db->fetchAssoc(
                "SELECT latitude, longitude, timezone
                FROM address_geolocate_queries WHERE
                (LCASE(address) = ?)
                ",
                [strtolower($address)]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    public static function checkInvalidQuery($address) {
        $cacheKey = $this->cacher->generateKey(self::ID_DB_checkInvalidQuery, [$address]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $db = self::getConnection();

        $result = $db->fetchAssoc(
                "SELECT id
                FROM address_geolocate_invalid WHERE
                (LCASE(query) = ?)
                ",
                [strtolower($address)]);

        $this->cacher->set($cacheKey, $result);

        return $result;
    }

    public static function getAddressCoOrdinatesAndZone($address)
    {
        if ($address == '' || $address == null) {
            return false;
        }
        $address = (string) $address;

        $cacheKey = $this->cacher->generateKey(self::ID_DB_getAddressCoOrdinatesAndZone, [$address]);

        if ($this->cacher->get($cacheKey) !== false) {
            return $this->cacher->get($cacheKey);
        }

        $checkAddress = self::checkAddressQuery($address);

        if ($checkAddress)
        {
            return $checkAddress;
        }

        $checkInvalidQuery = self::checkInvalidQuery($address);

        if ($checkInvalidQuery) {
            return false;
        }

        // If Google Bot is querying, return false or it's a self propelling cycle of Google making money!
        if (self::isGoogleBot()) {
            return false;
        }

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);

        self::writeLog('Sending Request :: geocode :: ' . json_encode(['address' => $address, $_REQUEST]) . ' ::: ');
        // Geocoding call.
        $res = $client->request('GET',
                         'geocode/json',
                         [
                             'query' => [
                                 'address' => $address,
                                 'key' => $this->appConfig->apiKey('google_geocoding')
                             ]
                         ]
                        );
        $r = (string) $res->getBody()->getContents();
        $x = json_decode($r);

        if ($x->status == 'OK') {
            $lat = $x->results[0]->geometry->location->lat;
            $lng = $x->results[0]->geometry->location->lng;

            // Timezone call.
            self::writeLog('Sending Request :: timezone :: ' . json_encode(['lat' => $lat, 'lng' => $lng, $_REQUEST]) . ' ::: ' );
            $res2 = $client->request('GET',
                         'timezone/json',
                         [
                             'query' => [
                                 'location' => $lat . ',' . $lng,
                                 'timestamp' => time(),
                                 'key' => $this->appConfig->apiKey('google_geocoding')
                             ]
                         ]
                        );
                $r2 = (string) $res2->getBody()->getContents();
                $x2 = json_decode($r2);

                if ($x2->status == 'OK') {
                    $timezone = $x2->timeZoneId;

                    // Update datbase
                    $db = self::getConnection();
                    $insert = $db->insert('address_geolocate_queries',
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
            self::recordInvalidQuery($address);

            return false;
        }
    }
}
