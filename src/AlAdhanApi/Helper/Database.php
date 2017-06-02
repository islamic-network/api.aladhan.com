<?php
namespace AlAdhanApi\Helper;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class Database
 * @package Database\Helper
 */

class Database
{

    const DB_NAME =  'aladhan_locations';

    /** MySQL database username */
    const DB_USER = '';

    /** MySQL database password */
    const DB_PASSWORD = '';

    /** MySQL hostname */
    const DB_HOSTNAME = '';
    
    const GOOGLE_GEOCODING_KEY = '';
    
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
        
        $connectionParams = array(
            'dbname' => self::DB_NAME,
            'user' => self::DB_USER,
            'password' => self::DB_PASSWORD,
            'host' => self::DB_HOSTNAME,
            'driver' => 'pdo_mysql',
        );

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public static function getCoOrdinatesAndTimezone($city, $country, $state = '')
    {
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
                                 'key' => self::GOOGLE_GEOCODING_KEY
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
                                 'key' => self::GOOGLE_GEOCODING_KEY
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
                    
                    return [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'timezone' => $timezone
                    ];
                }
            
            return false;
        }

        return false;
    }
    
    public static function checkGeolocateTable($city, $country, $state)
    {
        $db = self::getConnection();
        if ($state == '') {
            return $db->fetchAssoc("SELECT latitude, longitude, timezone 
                                FROM geolocate WHERE 
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                )", 
            [strtolower($country), strtolower($country),strtolower($city), strtolower($city)]);
        } else {
            return $db->fetchAssoc("SELECT latitude, longitude, timezone 
                                FROM geolocate WHERE 
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                AND
                                (LCASE(state) = ? OR LCASE(stateabbr) = ?)", 
            [strtolower($country), strtolower($country), strtolower($city), strtolower($city), strtolower($state), strtolower($state)]);
        }
    }
    
    private static function checkIfGeoRecordExistsViaCo($city, $country, $state) {
        $db = self::getConnection();
        return $db->fetchAssoc("SELECT id 
                                FROM geolocate WHERE 
                                city = ? AND country = ? AND state = ?", 
            [$city, $country, $state]);
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
        $db = self::getConnection();
        if ($state == '') {
            return $db->fetchAssoc(
                "SELECT latitude, longitude, timezone 
                FROM geolocate_queries WHERE
                (LCASE(country) = ?)
                AND
                (LCASE(city) = ?)
                ",
                [$country, $city]);
        } else {
            return $db->fetchAssoc(
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
    }
    
    public static function checkAddressQuery($address) 
    {
        $db = self::getConnection();
        
        return $db->fetchAssoc(
                "SELECT latitude, longitude, timezone 
                FROM address_geolocate_queries WHERE
                (LCASE(address) = ?)
                ",
                [strtolower($address)]);
    }
    
    public static function checkInvalidQuery($address) {
        $db = self::getConnection();
        
        return $db->fetchAssoc(
                "SELECT id 
                FROM address_geolocate_invalid WHERE
                (LCASE(query) = ?)
                ",
                [strtolower($address)]);
    }

    public static function getAddressCoOrdinatesAndZone($address)
    {
        if ($address == '' || $address == null) {
            return false;
        }
        $address = (string) $address;
        
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
                                 'key' => self::GOOGLE_GEOCODING_KEY
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
                                 'key' => self::GOOGLE_GEOCODING_KEY
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
                    return [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'timezone' => $timezone
                    ];
            }
        } else {
            self::recordInvalidQuery($address);
            
            return false;
        }
    }
}
 
 
