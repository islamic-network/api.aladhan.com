<?php
/**
 * PrayTimes.js: Prayer Times Calculator (ver 2.3)
 * Copyright (C) 2007-2011 PrayTimes.org

 * Developed in JavaScript by Hamid Zarrabi-Zadeh
 * Ported to PHP by Meezaan-ud-Din Abdu Dhil-Jalali Wal-Ikram
 * License: GNU LGPL v3.0
 */

namespace AlAdhanApi\Model;

use DateTime;
use DateTimezone;

/**
 * Class PrayerTimes
 */
class PrayerTimes
{
    /**
     * Constants for all items the times are computed for
     */
    const IMSAK = 'imsak';
    const FAJR = 'fajr';
    const SUNRISE = 'sunrise';
    const ZHUHR = 'dhuhr';
    const ASR = 'asr';
    const SUNSET = 'sunset';
    const MAGHRIB = 'maghrib';
    const ISHA = 'isha';
    const MIDNIGHT = 'midnight';

    /**
     * All methods available for computation
     */
    const METHOD_MWL = 'MWL'; // 3
    const METHOD_ISNA = 'ISNA'; // 2;
    const METHOD_EGYPT = 'EGYPT'; // 5;
    const METHOD_MAKKAH = 'MAKKAH'; // 4;
    const METHOD_KARACHI = 'KARACHI'; // 1;
    const METHOD_TEHRAN = 'TEHRAN'; // 7;
    const METHOD_JAFARI = 'JAFARI'; // 0;

    /**
     * Schools that determine the Asr shadow for the purpose of this class
     */
    const SCHOOL_STANDARD = 'STANDARD'; //0
    const SCHOOL_HANAFI = 'HANAFI'; // 1

    /**
     * Midnight Mode - how the midnight time is determined
     */
    const MIDNIGHT_MODE_STANDARD = 'STANDARD'; // Mid Sunset to Sunrise
    const MIDNIGHT_MODE_JAFARI = 'JAFARI'; // Mid Sunset to Fajr

    /**
     * Higher Latitude Adjustment Methods
     */
    const LATITUDE_ADJUSTMENT_METHOD_MOTN = 'MIDDLE_OF_THE_NIGHT'; // 1
    const LATITUDE_ADJUSTMENT_METHOD_ANGLE = 'ANGLE_BASED'; // 3, angle/60th of night
    const LATITUDE_ADJUSTMENT_METHOD_ONESEVENTH = 'ONE_SEVENTH'; // 2
    const LATITUDE_ADJUSTMENT_METHOD_NONE = 'NONE'; // 0

    /**
     * Formats in which data can be output
     */
    const TIME_FORMAT_24H = '24h'; // 24-hour format
    const TIME_FORMAT_12H = '12h'; // 12-hour format
    const TIME_FORMAT_12hNS = '12hNS'; // 12-hour format with no suffix
    const TIME_FORMAT_FLOAT = 'Float'; // floating point number

    /**
     * If we're unable to calculate a time, we'll return this
     */
    const INVALID_TIME = '-----';

    /**
     * @Array
     */
    public $methods;

    /**
     * @Array
     */
    public $methodCodes;

    /**
     * @object DateTime
     */
    private $date;

    /**
     * @String
     */
    private $method;

    /**
     * @Sstring
     */
    private $school = self::SCHOOL_STANDARD;

    /**
     * @String
     */
    private $midnightMode;

    /**
     * @String
     */
    private $latitudeAdjustmentMethod;

    /**
     * @String
     */
    private $timeFormat;

    /**
     * @String
     */
    private $latitude;

    /**
     * @String
     */
    private $longitude;

    /**
     * @String
     */
    private $elevation;

    /**
     * @String
     */
    private $asrShadowFactor = null;

    /**
     * @String
     */
    private $settings;

    /**
     * @String
     */
    private $offset = [];


    /**
     * @param string $method
     * @param string $school
     * @param null $asrShadowFactor If specified, this will override the school setting
     * @param array|null $offset
     */
    public function __construct($method = self::METHOD_MWL, $school = self::SCHOOL_STANDARD, $asrShadowFactor = null)
    {
        $this->loadMethods();
        $this->setMethod($method);
        $this->setSchool($school);
        if ($asrShadowFactor !== null) {
            $this->asrShadowFactor = $asrShadowFactor;
        }
        $this->loadSettings();
    }

    /**
     *
     */
    private function loadSettings()
    {
        $this->settings = new \stdClass();
        $this->settings->{self::IMSAK} = isset($this->methods[$this->method]['params'][self::IMSAK]) ? $this->methods[$this->method]['params'][self::IMSAK] : '10 min';
        $this->settings->{self::FAJR} = isset($this->methods[$this->method]['params'][self::FAJR]) ? $this->methods[$this->method]['params'][self::FAJR] : 0;
        $this->settings->{self::ZHUHR} = isset($this->methods[$this->method]['params'][self::ZHUHR]) ? $this->methods[$this->method]['params'][self::ZHUHR] : '0 min';
        $this->settings->{self::ISHA} = isset($this->methods[$this->method]['params'][self::ISHA]) ? $this->methods[$this->method]['params'][self::ISHA] : 0;
        $this->settings->{self::MAGHRIB} = isset($this->methods[$this->method]['params'][self::MAGHRIB]) ? $this->methods[$this->method]['params'][self::MAGHRIB] : '0 min';
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $timezone
     * @param null $elevation
     * @param string $latitudeAdjustmentMethod
     * @param string $midnightMode
     * @param string $format
     * @return mixed
     */
    public function getTimesForToday($latitude, $longitude, $timezone, $elevation = null, $latitudeAdjustmentMethod = self::LATITUDE_ADJUSTMENT_METHOD_ANGLE, $midnightMode = self::MIDNIGHT_MODE_STANDARD, $format = self::TIME_FORMAT_24H)
    {
        $date = new DateTime(null, new DateTimezone($timezone));

        return $this->getTimes($date, $latitude, $longitude, $elevation, $latitudeAdjustmentMethod, $midnightMode, $format);
    }

    /**
     * @param DateTime $date
     * @param $latitude
     * @param $longitude
     * @param null $elevation
     * @param string $latitudeAdjustmentMethod
     * @param string $midnightMode
     * @param string $format
     * @return mixed
     */
    public function getTimes(DateTime $date, $latitude, $longitude, $elevation = null, $latitudeAdjustmentMethod = self::LATITUDE_ADJUSTMENT_METHOD_ANGLE, $midnightMode = self::MIDNIGHT_MODE_STANDARD, $format = self::TIME_FORMAT_24H)
    {
        $this->latitude = 1 * $latitude;
        $this->longitude = 1 * $longitude;
        $this->elevation = $elevation === null ? 0 : 1 * $elevation;
        $this->setTimeFormat($format);
        $this->setLatitudeAdjustmentMethod($latitudeAdjustmentMethod);
        $this->setMidnightMode($midnightMode);
        $this->date = $date;

        return $this->computeTimes();
    }

    /**
     * @return Array
     */
    private function computeTimes()
    {
        // default times
        $times = [
            self::IMSAK => 5,
            self::FAJR => 5,
            self::SUNRISE => 6,
            self::ZHUHR => 12,
            self::ASR => 13,
            self::SUNSET => 18,
            self::MAGHRIB => 18,
            self::ISHA => 18
        ];

        $times = $this->computePrayerTimes($times);

        $times = $this->adjustTimes($times);

        // add midnight time
        $times[self::MIDNIGHT] = ($this->midnightMode == 'Jafari') ? $times[self::SUNSET] + $this->timeDiff($times[self::SUNSET], $times[self::FAJR]) / 2 : $times[self::SUNSET] + $this->timeDiff($times[self::SUNSET], $times[self::SUNRISE]) / 2;



        $times = $this->tuneTimes($times);

        // Make keys uppercase.
        $times = array_combine(
            array_map('ucfirst', array_keys($times)),
            array_values($times)
        );

        return $this->modifyFormats($times);
    }

    /**
     * @param $times
     * @return Array
     */
    private function modifyFormats($times)
    {
        foreach ($times as $i => $t) {
            $times[$i] = $this->getFormattedTime($t, $this->timeFormat);
        }

        return $times;
    }

    /**
     * @param $time
     * @param $format
     * @return string
     */
    private function getFormattedTime($time, $format)
    {
        if (is_nan($time)) {
            return self::INVALID_TIME;
        }
        if ($format == self::TIME_FORMAT_FLOAT) {
            return $time;
        }
        $suffixes = ['am', 'pm'];

        $time = DMath::fixHour($time + 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time - $hours)* 60);
        $suffix = ($this->timeFormat == self::TIME_FORMAT_12H) ? $suffixes[$hours < 12 ? 0 : 1] : '';
        $hour = ($format == self::TIME_FORMAT_24H) ? $this->twoDigitsFormat($hours) : (($hours+ 12 -1)% 12+ 1);

        return $hour . ':' . $this->twoDigitsFormat($minutes) . ($suffix ? ' ' . $suffix : '');
    }

    /**
     * @param $num
     * @return string
     */
    private function twoDigitsFormat($num)
    {
        return ($num <10) ? '0'. $num : $num;
    }

    /**
     * @param $times
     * @return mixed
     */
    private function tuneTimes($times)
    {
        if (!empty($this->offset)) {
            foreach ($times as $i => $t) {
                $times[$i] += $this->offset[$i] / 60;
            }
        }

        return $times;
    }

    /**
     * @param $str
     * @return mixed
     */
    private function evaluate($str)
    {
        //$str = preg_replace('/\D/', '', $str);

        return floatval($str);
    }

    /**
     * @param $times
     * @return mixed
     */
    private function adjustTimes($times)
    {
        $dateTimeZone = $this->date->getTimezone();

        foreach ($times as $i => $t) {
            $times[$i] += ($dateTimeZone->getOffset($this->date)/3600) - $this->longitude / 15;
        }
        if ($this->latitudeAdjustmentMethod != self::LATITUDE_ADJUSTMENT_METHOD_NONE) {
            $times = $this->adjustHighLatitudes($times);
        }

        if ($this->isMin($this->settings->{self::IMSAK})) {
            $times[self::IMSAK] = $times[self::FAJR] - $this->evaluate($this->settings->{self::IMSAK})/ 60;
        }
        if ($this->isMin($this->settings->{self::MAGHRIB})) {
            $times[self::MAGHRIB] = $times[self::SUNSET] + $this->evaluate($this->settings->{self::MAGHRIB})/ 60;
        }
        if ($this->isMin($this->settings->{self::ISHA})) {
            $times[self::ISHA] = $times[self::MAGHRIB] + $this->evaluate($this->settings->{self::ISHA})/ 60;
        }
        $times[self::ZHUHR] += $this->evaluate($this->settings->{self::ZHUHR})/ 60;

        return $times;
    }

    /**
     * @param $times
     * @return mixed
     */
    private function adjustHighLatitudes($times)
    {
        $nightTime = $this->timeDiff($times[self::SUNSET], $times[self::SUNRISE]);

        $times[self::IMSAK] = $this->adjustHLTime($times[self::IMSAK], $times[self::SUNRISE], $this->evaluate($this->settings->{self::IMSAK}), $nightTime, 'ccw');
        $times[self::FAJR]  = $this->adjustHLTime($times[self::FAJR], $times[self::SUNRISE], $this->evaluate($this->settings->{self::FAJR}), $nightTime, 'ccw');
        $times[self::ISHA]  = $this->adjustHLTime($times[self::ISHA], $times[self::SUNSET], $this->evaluate($this->settings->{self::ISHA}), $nightTime);
        $times[self::MAGHRIB] = $this->adjustHLTime($times[self::MAGHRIB], $times[self::SUNSET], $this->evaluate($this->settings->{self::MAGHRIB}), $nightTime);

        return $times;
    }

    /**
     * @param $str
     * @return bool
     */
    private function isMin($str)
    {
        if (strpos($str, 'min') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param $time
     * @param $base
     * @param $angle
     * @param $night
     * @param null $direction
     * @return mixed
     */
    private function adjustHLTime($time, $base, $angle, $night, $direction = null)
    {
        $portion = $this->nightPortion($angle, $night);
        $timeDiff = ($direction == 'ccw') ? $this->timeDiff($time, $base): $this->timeDiff($base, $time);
        if (is_nan($time) || $timeDiff > $portion) {
            $time = $base + ($direction == 'ccw' ? (- $portion) : $portion);
        }

        return $time;
    }

    /**
     * @param $angle
     * @param $night
     * @return float
     */
    private function nightPortion($angle, $night)
    {
        $method = $this->latitudeAdjustmentMethod;
        $portion = 1/2; // MidNight
        if ($method == self::LATITUDE_ADJUSTMENT_METHOD_ANGLE) {
            $portion = 1/60 * $angle;
        }
        if ($method == self::LATITUDE_ADJUSTMENT_METHOD_ONESEVENTH) {
            $portion = 1/7;
        }
        return $portion * $night;
    }

    /**
     * @param $t1
     * @param $t2
     * @return mixed
     */
    private function timeDiff($t1, $t2)
    {
        return DMath::fixHour($t2 - $t1);
    }

    /**
     * @param $times
     * @return array
     */
    private function computePrayerTimes($times)
    {
        $times = $this->dayPortion($times);
        $imsak   = $this->sunAngleTime($this->evaluate($this->settings->{self::IMSAK}), $times[self::IMSAK], 'ccw');
        $fajr    = $this->sunAngleTime($this->evaluate($this->settings->{self::FAJR}), $times[self::FAJR], 'ccw');
        $sunrise = $this->sunAngleTime($this->riseSetAngle(), $times[self::SUNRISE], 'ccw');
        $dhuhr   = $this->midDay($times[self::ZHUHR]);
        $asr     = $this->asrTime($this->asrFactor(), $times[self::ASR]);
        $sunset  = $this->sunAngleTime($this->riseSetAngle(), $times[self::SUNSET]);
        $maghrib = $this->sunAngleTime($this->evaluate($this->settings->{self::MAGHRIB}), $times[self::MAGHRIB]);
        $isha    = $this->sunAngleTime($this->evaluate($this->settings->{self::ISHA}), $times[self::ISHA]);

        return [
            self::FAJR => $fajr,
            self::SUNRISE => $sunrise,
            self::ZHUHR => $dhuhr,
            self::ASR => $asr,
            self::SUNSET => $sunset,
            self::MAGHRIB => $maghrib,
            self::ISHA => $isha,
            self::IMSAK => $imsak,
        ];
    }

    /**
     * @param $factor
     * @param $time
     * @return mixed
     */
    private function asrTime($factor, $time)
    {
        $julianDate = GregorianToJD($this->date->format('n'), $this->date->format('d'), $this->date->format('Y'));
        $decl = $this->sunPosition($julianDate + $time)->declination;
        $angle = -DMath::arccot($factor+ DMath::tan(abs($this->latitude- $decl)));

        return $this->sunAngleTime($angle, $time);
    }

    /**
     * @return int|null
     */
    private function asrFactor()
    {
        if ($this->asrShadowFactor !== null) {
            return $this->asrShadowFactor;
        }
        if ($this->school == self::SCHOOL_STANDARD) {
            return 1;
        } elseif ($this->school == self::SCHOOL_HANAFI) {
            return 2;
        } else {
            return 0;
        }
    }

    /**
     * @return float
     */
    private function riseSetAngle()
    {
        //var earthRad = 6371009; // in meters
        //var angle = DMath.arccos(earthRad/(earthRad+ elv));
        $angle = 0.0347* sqrt($this->elevation); // an approximation

        return 0.833+ $angle;
    }

    /**
     * @param $angle
     * @param $time
     * @param null $direction
     * @return mixed
     */
    // compute the time at which sun reaches a specific angle below horizon
    private function sunAngleTime($angle, $time, $direction = null)
    {
        $julianDate = $this->julianDate($this->date->format('Y'), $this->date->format('n'), $this->date->format('d')) - $this->longitude/ (15* 24);
        $decl = $this->sunPosition($julianDate + $time)->declination;
        $noon = $this->midDay($time);
        $t = 1/15 * DMath::arccos((-DMath::sin($angle) - DMath::sin($decl) * DMath::sin($this->latitude)) /
                (DMath::cos($decl) * DMath::cos($this->latitude)));

        return $noon + ($direction == 'ccw' ? -$t : $t);
    }

    /**
     * @param $julianDate
     * @return stdClass
     */
    private function sunPosition($julianDate)
    {
        // compute declination angle of sun and equation of time
        // Ref: http://aa.usno.navy.mil/faq/docs/SunApprox.php
        $D = $julianDate - 2451545.0;
        $g = DMath::fixAngle(357.529 + 0.98560028* $D);
        $q = DMath::fixAngle(280.459 + 0.98564736* $D);
        $L = DMath::fixAngle($q + 1.915 * DMath::sin($g) + 0.020 * DMath::sin(2*$g));

        $R = 1.00014 - 0.01671* DMath::cos($g) - 0.00014* DMath::cos(2*$g);
        $e = 23.439 - 0.00000036* $D;

        $RA = DMath::arctan2(DMath::cos($e)* DMath::sin($L), DMath::cos($L))/ 15;
        $eqt = $q/15 - DMath::fixHour($RA);
        $decl = DMath::arcsin(DMath::sin($e)* DMath::sin($L));

        $res = new \stdClass();
        $res->declination = $decl;
        $res->equation = $eqt;

        return $res;
    }

    /**
     * @param $year
     * @param $month
     * @param $day
     * @return float
     */
    private function julianDate($year, $month, $day)
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year/ 100);
        $B = 2- $A+ floor($A/ 4);

        $JD = floor(365.25* ($year+ 4716))+ floor(30.6001* ($month+ 1))+ $day+ $B- 1524.5;

        return $JD;
    }

    /**
     * @param $time
     * @return mixed
     */
    private function midDay($time)
    {
        $julianDate = $this->julianDate($this->date->format('Y'), $this->date->format('n'), $this->date->format('d')) - $this->longitude/ (15* 24);
        $eqt = $this->sunPosition($julianDate + $time)->equation;
        $noon = DMath::fixHour(12 - $eqt);

        return $noon;
    }

    /**
     * @param $times
     * @return mixed
     */
    private function dayPortion($times)
    {
        // convert hours to day portions
        foreach ($times as $i => $time) {
            $times[$i] = $time / 24;
        }

        return $times;
    }

    /**
     * @param string $method
     */
    public function setMethod($method = self::METHOD_MWL)
    {
        if (in_array($method, $this->methodCodes)) {
            $this->method = $method;
        } else {
            $this->method = self::METHOD_MWL; // Default to MWL
        }
    }

    /**
     * @param string $method
     */
    public function setAsrJuristicMethod($method = self::SCHOOL_STANDARD)
    {
        if (in_array($method, [self::SCHOOL_HANAFI, self::SCHOOL_STANDARD])) {
            $this->school = $method;
        } else {
            $this->school = self::SCHOOL_STANDARD;
        }
    }

    /**
     * @param string $school
     */
    public function setSchool($school = self::SCHOOL_STANDARD)
    {
        $this->setAsrJuristicMethod($school);
    }

    /**
     * @param string $mode
     */
    public function setMidnightMode($mode = self::MIDNIGHT_MODE_STANDARD)
    {
        if (in_array($mode, [self::MIDNIGHT_MODE_JAFARI, self::MIDNIGHT_MODE_STANDARD])) {
            $this->midnightMode = $mode;
        } else {
            $this->midnightMode = self::MIDNIGHT_MODE_STANDARD;
        }
    }

    /**
     * @param string $method
     */
    public function setLatitudeAdjustmentMethod($method = self::LATITUDE_ADJUSTMENT_METHOD_ANGLE)
    {
        if (in_array($method, [self::LATITUDE_ADJUSTMENT_METHOD_MOTN, self::LATITUDE_ADJUSTMENT_METHOD_ANGLE, self::LATITUDE_ADJUSTMENT_METHOD_ONESEVENTH, self::LATITUDE_ADJUSTMENT_METHOD_NONE ])) {
            $this->latitudeAdjustmentMethod = $method;
        } else {
            $this->latitudeAdjustmentMethod = self::LATITUDE_ADJUSTMENT_METHOD_ANGLE;
        }
    }

    /**
     * @param string $format
     */
    public function setTimeFormat($format = self::TIME_FORMAT_24H)
    {
        if (in_array($format, [self::TIME_FORMAT_24H, self::TIME_FORMAT_FLOAT, self::TIME_FORMAT_12hNS, self::TIME_FORMAT_12H])) {
            $this->timeFormat = $format;
        } else {
            $this->timeFormat = self::TIME_FORMAT_24H;
        }
    }

    /**
     * @param int $imsak
     * @param int $fajr
     * @param int $sunrise
     * @param int $dhuhr
     * @param int $asr
     * @param int $maghrib
     * @param int $sunset
     * @param int $isha
     * @param int $midnight
     */
    public function tune($imsak = 0, $fajr = 0, $sunrise = 0, $dhuhr = 0, $asr = 0, $maghrib = 0, $sunset = 0, $isha = 0, $midnight = 0)
    {
        $this->offset = [
            self::IMSAK => $imsak,
            self::FAJR => $fajr,
            self::SUNRISE => $sunrise,
            self::ZHUHR => $dhuhr,
            self::ASR => $asr,
            self::MAGHRIB => $maghrib,
            self::SUNSET => $sunset,
            self::ISHA => $isha,
            self::MIDNIGHT => $midnight
        ];
    }


    /**
     * Loads all the default settings for calculation methods
     */
    public function loadMethods()
    {
        $this->methods = [
            self::METHOD_MWL => [
                'name' => 'Muslim World League',
                'params' => [
                    self::FAJR => 18,
                    self::ISHA => 17
                ]
            ],
            self::METHOD_ISNA => [
                'name' => 'Islamic Society of North America (ISNA)',
                'params' => [
                    self::FAJR => 15,
                    self::ISHA => 15
                ]
            ],
            self::METHOD_EGYPT => [
                'name' => 'Egyptian General Authority of Survey',
                'params' => [
                    self::FAJR => 19.5,
                    self::ISHA => 17.5
                ]
            ],
            self::METHOD_MAKKAH => [
                'name' => 'Umm Al-Qura University, Makkah',
                'params' => [
                    self::FAJR => 18.5, // fajr was 19 degrees before 1430 hijri
                    self::ISHA => '90 min'
                ]
            ],
            self::METHOD_KARACHI => [
                'name' => 'University of Islamic Sciences, Karachi',
                'params' => [
                    self::FAJR => 18,
                    self::ISHA => 18
                ]
            ],
            self::METHOD_TEHRAN => [
                'name' => 'Institute of Geophysics, University of Tehran',
                'params' => [
                    self::FAJR => 17.7,
                    self::ISHA => 14,
                    self::MAGHRIB => 4.5,
                    self::MIDNIGHT => self::METHOD_JAFARI // isha is not explicitly specified in this method
                ]
            ],
            self::METHOD_JAFARI => [
                'name' => 'Shia Ithna-Ashari, Leva Institute, Qum',
                'params' => [
                    self::FAJR => 16,
                    self::ISHA => 14,
                    self::MAGHRIB => 4,
                    self::MIDNIGHT => self::METHOD_JAFARI
                ]
            ]

        ];

        $this->methodCodes = [
            self::METHOD_MWL, self::METHOD_ISNA, self::METHOD_EGYPT, self::METHOD_MAKKAH, self::METHOD_KARACHI, self::METHOD_TEHRAN, self::METHOD_JAFARI
        ];
    }

    public function getMethod()
    {
        return $this->method;
    }
}



/**
 * Class DMath
 */
class DMath
{
    public static function dtr($d)
    {
        return ($d * pi()) / 180.0;
    }
    public static function rtd($r)
    {
        return ($r * 180.0) / pi();
    }

    public static function sin($d)
    {
        return sin(self::dtr($d));
    }
    public static function cos($d)
    {
        return cos(self::dtr($d));
    }
    public static function tan($d)
    {
        return tan(self::dtr($d));
    }

    public static function arcsin($d)
    {
        return self::rtd(asin($d));
    }
    public static function arccos($d)
    {
        return self::rtd(acos($d));
    }
    public static function arctan($d)
    {
        return self::rtd(atan($d));
    }

    public static function arccot($x)
    {
        return self::rtd(atan(1/$x));
    }
    public static function arctan2($y, $x)
    {
        return self::rtd(atan2($y, $x));
    }

    public static function fixAngle($a)
    {
        return self::fix($a, 360);
    }
    public static function fixHour($a)
    {
        return self::fix($a, 24 );
    }

    public static function fix($a, $b)
    {
        $a = $a - $b * (floor($a / $b));
        return ($a < 0) ? $a + $b : $a;
    }
}
