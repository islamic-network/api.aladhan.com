<?php

namespace AlAdhanApi;

use DateTime;
use DateTimeZone;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ServiceHelper {

    public $restService;
    public $compute;
    public $method;
    public $date;
    public $school;

    public $month;
    public $year;
    
    public $latitudeAdjustmentMethod = 3;
    
    public $db;
    
    public $logger;

    // ** MySQL settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */

    const DB_NAME =  'aladhan_locations';

    /** MySQL database username */
    const DB_USER = 'aladhancom';

    /** MySQL database password */
    const DB_PASSWORD = '9q2Tfam3B8pxeGF';

    /** MySQL hostname */
    const DB_HOSTNAME = 'localhost';
    
    const GOOGLE_GEOCODING_KEY = 'AIzaSyDhwVAK767png0ntsoWHgwtgLp_gg9qh3s';
    
    
    public function __construct($restService)
    {
        $this->restService = $restService;
        $this->compute = true;
        $logId = uniqid();
        $logStamp = time();
        $logFile = 'Google_' . date('Y-m-d', $logStamp);
        $logTime = $logId . ' :: ' . date('Y-m-d H:i:s :: ');
        // Create the logger
        $this->logger = new Logger('GoogleEndpoint');
        // Now add some handlers
        $this->logger->pushHandler(new StreamHandler(realpath(__DIR__ . '/../../logs/') . '/' .$logFile . '.log', Logger::INFO));
    }

    public function validateLatitude()
    {
        if (!isset($this->restService->request_data['latitude']) || !is_numeric($this->restService->request_data['latitude'])) {
            $this->restService->setResponseCode(400);
            $this->restService->setResponse('Please specify a latitude value.');
            $this->compute = false;
        }
    }

    public function validateLongitude()
    {
        if (!isset($this->restService->request_data['longitude']) || !is_numeric($this->restService->request_data['longitude'])) {
            $this->restService->setResponseCode(400);
            $this->restService->setResponse('Please specify a longitude value.');
            $this->compute = false;
        }
    }

    public function validateTimezone()
    {
        if (!isset($this->restService->request_data['timezone']) || !is_numeric($this->restService->request_data['timezone'])) {
            $this->restService->setResponseCode(400);
            $this->restService->setResponse('Please specify a timezone as a GMT offset. Example: Pass "1" for a location at GMT/UTC + 1 hour.');
            $this->compute = false;
        }
    }

    public function validateTimezoneString()
    {
        if (!isset($this->restService->request_data['timezonestring']) || empty($this->restService->request_data['timezonestring']) || !in_array($this->restService->request_data['timezonestring'], $this->getAllTimeZones())) {
            $this->restService->setResponseCode(400);
            $this->restService->setResponse('Please specify a valid timezone string. Example: Europe/London. You can find a complete list on http://php.net/manual/en/timezones.php.');
            $this->compute = false;
        }

    }

    public function getAllTimeZones()
    {
        $zones = array();
        $x = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        foreach($x as $key => $zone) {
            $zones[] = $zone;
        }

        return $zones;
    }

    public function setHighLatitudeAdjustmentMethod()
    {
        if (!isset($this->restService->request_data['latitudeAdjustmentMethod']) || !in_array($this->restService->request_data['latitudeAdjustmentMethod'], array(1, 2, 3)) || $this->restService->request_data['latitudeAdjustmentMethod'] == '') {
            $this->latitudeAdjustmentMethod = 3;
        } else {
             $this->latitudeAdjustmentMethod = $this->restService->request_data['latitudeAdjustmentMethod'];
        }
    }
    
    public function setMethod()
    {
        if (!isset($this->restService->request_data['method']) || !in_array($this->restService->request_data['method'], array(0, 1, 2, 3, 4, 5, 7)) || $this->restService->request_data['method'] == '') {
            $this->method = 2; // Default to ISNA.
        } else {
            $this->method = $this->restService->request_data['method'];
        }
    }

    public function setTime()
    {
        if (!isset($this->restService->request_data['resource_id']) || !is_numeric($this->restService->request_data['resource_id']) || $this->restService->request_data['resource_id'] < 1) {
            $this->date = time(); // Today.
        } else {
            $this->date = $this->restService->request_data['resource_id'];
        }
    }
    
    public function setSchool() {
        if (!isset($this->restService->request_data['school']) || !in_array($this->restService->request_data['school'], array(0, 1))) {
            $this->school = 0; // Default to Shafi.
        } else {
            $this->school = (int) $this->restService->request_data['school'];
        }
    }

    public function getTimeZoneOffsetString($timezoneString = '') {
        if ($timezoneString == '') {
            $timezoneString = !isset($this->restService->request_data['timezonestring']) ? 'Europe/London' : $this->restService->request_data['timezonestring'];
        }
        $dt = new DateTime( date('Y-m-d H:i:s', $this->date) , new DateTimeZone($timezoneString));
        $gmt_offset_in_seconds = $dt->format('Z');
        $gmt_offset_in_hours = $gmt_offset_in_seconds/3600; // 3600 seconds in hours

        return $gmt_offset_in_hours;
    }

    public function setCalendarStartTime()
    {
        if (!isset($this->restService->request_data['month']) 
            || !isset($this->restService->request_data['year']))  {
                $now = time();
                $this->month = date('m', $now);
                $this->year = date('Y', $now);
            } else {
                $this->month = (int) $this->restService->request_data['month'];
                $this->year = (int) $this->restService->request_data['year'];
            }
    }

    public function getCalendar($latitude = '', $longitude = '', $timezoneString = '')
    {
        if ($timezoneString == '') {
            $timezoneString = !isset($this->restService->request_data['timezonestring']) ? 'Europe/London' : $this->restService->request_data['timezonestring'];
        }
        if ($latitude == '') {
            $latitude = $this->restService->request_data['latitude'];
        }
        if ($longitude == '') {
            $longitude = $this->restService->request_data['longitude'];
        }
        date_default_timezone_set($timezoneString);
        $cal_start = strtotime($this->year . '-' . $this->month . '-01 09:01:01');
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $this->restService->PrayTime($this->method, $this->school, $this->latitudeAdjustmentMethod);
        $times = array();
        // We won't use the passed timezone here anymore because of daylight savinbs. Instead, we'll simply take the timezone string and compute the GMT offset and pass that to the pray times class.
        for ($i = 0; $i <= ($days_in_month -1); $i++) {
            // Create date time object for this date.
            $calstart = new \DateTime( date('Y-m-d H:i:s', $cal_start) , new \DateTimeZone($timezoneString));
            $gmt_offset_in_seconds = $calstart->format('Z');
            $gmt_offset_in_hours = $gmt_offset_in_seconds/3600; // 3600 seconds in hours
            $timings =  $this->restService->getPrayerTimesAndNames($calstart->format('U'), $latitude, $longitude, $gmt_offset_in_hours);
            foreach ($timings['timings'] as $key => $t) {
               if (strpos($t, ':') !== false) {
                    $dt = new \DateTime(date('Y-m-d', $timings['date']['timestamp']) . ' ' . $t . ':00', new \DateTimeZone($timezoneString));
                    $ourtime = $dt->format('H:i (T)');
                    $timings['timings'][$key] = $ourtime;
               }
            }
            $times[$i] =  $timings;
            // Add 24 hours to start date
            $cal_start =  $cal_start + (1*60*60*24);
        }

        return $times;
    }
    
    public function logArray($server, $request) {
        $l = [];
        // Request Params
        $l['request'] = $request;
        $l['server'] = [
            'ip' => $server['REMOTE_ADDR'],
            'url' => $server['SCRIPT_URL'],
            'method' => $server['REQUEST_METHOD']
        ];
        if (isset($server['HTTP_USER_AGENT'])) {
            $l['server']['useragent'] = $server['HTTP_USER_AGENT'];
        }
        if (isset($server['HTTP_ORIGIN'])) {
            $l['server']['origin'] = $server['HTTP_ORIGIN'];
        }
        if (isset($server['HTTP_REFERER'])) {
            $l['server']['referer'] = $server['HTTP_REFERER'];
        }
        
        
        return $l;
    }
    
    public function loadDbConnection()
    {
        $config = new \Doctrine\DBAL\Configuration();
        
        $connectionParams = array(
            'dbname' => self::DB_NAME,
            'user' => self::DB_USER,
            'password' => self::DB_PASSWORD,
            'host' => self::DB_HOSTNAME,
            'driver' => 'pdo_mysql',
        );

        $this->db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }
    
    public function getCoOrdinatesAndTimezone($city, $country, $state = '')
    {
        $this->loadDbConnection();
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
        
        return $result;
        
    }
    
    public function getGoogleCoOrdinatesAndZone($city, $country, $state = '')
    {
        $cityO = $city;
        $countryO = $country;
        $stateO = $state;
        
        if (!$this->citySanitizer($city, $country)) {
            return false;
        }
        
        $checkQuery = $this->checkQuery($cityO, $countryO, $stateO);
        
        if ($checkQuery) {
            return $checkQuery;
        }
        
        $local = $this->checkGeolocateTable($city, $country, $state);

        if ($local) {
            return $local;
        }
        
        $this->logger->addInfo('Sending Request :: geocode :: ', ['city' => $city, 'country' => $country, 'state' => $state, $_SERVER, $_REQUEST]);
        
        $string = $city;
        if ($state != '') {
            $string .= ', ' . $state;
        }
        $string .= ', ' . $country;
        
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
        
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
            $this->logger->addInfo('Sending Request :: timezone :: ', ['lat' => $lat, 'lng' => $lng, $_SERVER, $_REQUEST]);
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
                    
                    $this->loadDbConnection();
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
    
    public function checkGeolocateTable($city, $country, $state)
    {
        $this->loadDbConnection();
        if ($state == '') {
            return $this->db->fetchAssoc("SELECT latitude, longitude, timezone 
                                FROM geolocate WHERE 
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                OR
                                (LCASE(state) = ? OR LCASE(stateabbr) = ?)
                                )", 
            [strtolower($country), strtolower($country),strtolower($city), strtolower($city), strtolower($city), strtolower($city)]);
        } else {
            return $this->db->fetchAssoc("SELECT latitude, longitude, timezone 
                                FROM geolocate WHERE 
                                (LCASE(country) = ? OR LCASE(countryiso) = ?)
                                AND
                                (LCASE(city) = ? OR LCASE(cityabbr) = ?)
                                AND
                                (LCASE(state) = ? OR LCASE(stateabbr) = ?)", 
            [strtolower($country), strtolower($country), strtolower($city), strtolower($city), strtolower($state), strtolower($state)]);
        }
    }
    
    private function checkIfGeoRecordExistsViaCo($city, $country, $state) {
        $this->loadDbConnection();
        return $this->db->fetchAssoc("SELECT id 
                                FROM geolocate WHERE 
                                city = ? AND country = ? AND state = ?", 
            [$city, $country, $state]);
    }
    
    public function citySanitizer($city, $country, $state = '')
    {
        $city = strtolower($city);
        $country = strtolower($country);
        
        if ($city == '' || $country == '') {
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
    
        public function recordQuery($city, $state, $country, $lat, $lng, $timezone)
    {
        $this->loadDbConnection();
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
    
    public function checkQuery($city, $country, $state)
    {
        $this->loadDbConnection();
        if ($state == '') {
            return $this->db->fetchAssoc(
                "SELECT latitude, longitude, timezone 
                FROM geolocate_queries WHERE
                (LCASE(country) = ?)
                AND
                (LCASE(city) = ?)
                ",
                [$country, $city]);
        } else {
            return $this->db->fetchAssoc(
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
    
    public function checkAddressQuery($address) 
    {
        $this->loadDbConnection();
        return $this->db->fetchAssoc(
                "SELECT latitude, longitude, timezone 
                FROM address_geolocate_queries WHERE
                (LCASE(address) = ?)
                ",
                [strtolower($address)]);
    }

    public function getAddressCoOrdinatesAndZone($address)
    {
        if ($address == '') {
            return false;
        }
        $address = (string) $address;
        
        $checkAddress = $this->checkAddressQuery($address);
        
        if ($checkAddress)
        {
            return $checkAddress;
        }
        
        $this->logger->addInfo('Sending Request :: ', ['address' => $address, $_SERVER, $_REQUEST]);
        
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
        
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
                    $this->loadDbConnection();
                    $insert = $this->db->insert('address_geolocate_queries', 
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
        }  
    }
}
