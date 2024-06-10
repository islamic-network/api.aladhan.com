<?php

namespace Api\Utils;

use AlAdhanApi\Model\HijriCalendarService;
use Api\Models\HijriCalendar;
use Haversini\Haversini;
use IslamicNetwork\MoonSighting\Isha;
use IslamicNetwork\PrayerTimes\Method;
use Emoji;

/**
 * Class Request
 * @package Helper\Request
 */
class Request
{

    public static function school(mixed $data): int
    {
        if (in_array($data, [0, 1])) {
            return (int) $data;
        }

        return (int) 0;
    }

    public static function shafaq($data): string
    {
        if (in_array($data, [Isha::SHAFAQ_GENERAL, Isha::SHAFAQ_AHMER, Isha::SHAFAQ_ABYAD])) {
            return $data;
        }

        return Isha::SHAFAQ_GENERAL;
    }

    public static function midnightMode($data): int
    {
        if (in_array($data, [0, 1], true)) {
            return $data;
        }

        return 0;
    }

    public static function latitudeAdjustmentMethod($data): int
    {
        if (!in_array($data, [0, 1, 2, 3])) {
            return 3;
        }

        return (int) $data;
    }

    public static function method($data, $latitude = null, $longitude= null): int
    {
        if ($data == 'null' || $data == '') {
            // Calculate Method user Haversine
            if ($latitude !== null && $longitude !== null) {
                return self::calculateClosestMethod($latitude, $longitude);
            }

            return 2; // ISNA
        }
        if (!in_array($data,
            [0, 1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 99]
        )) {
            return 2; // ISNA
        } else {
            return (int) $data;
        }
    }

    public static function customMethod($data): array
    {
        $result = [];

        if ($data !== null) {
            $method = explode(',', $data);
            // PrayerTimesHelper::createCustomMethod() takes a total of 12 params
            for ($i=0; $i<=11; $i++) {
                if (isset($method[$i]) && $method[$i] != 0 && $method[$i] != null) {
                    $result[] = (string) $method[$i];
                } else {
                    $result[] = null;
                }
            }
        }

        return $result;
    }

    public static function tune(?string $data): array
    {
        $result = [];
        $method = explode(',', $data);
        // PrayerTimes::tune() takes a total of 9 params
        for ($i = 0; $i <= 8; $i++) {
            if (isset($method[$i]) && $method[$i] != 0 && $method[$i] != null) {
                $result[] = (string) $method[$i];
            } else {
                $result[] = 0;
            }
        }

        return $result;
    }

    /**
     * @param string $timestamp
     * @return bool
     */
    public static function isUnixTimeStamp(string $timestamp): bool
    {

        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
     }

    /**
     * Accepts a date or unix timestamp and returns a unix timestamp
     * @param string $data
     * @return int
     */
    public static function time(string $data, string $timezone = 'UTC'): int
    {
        // If it is a timestamp, just return it.
        if (self::isUnixTimeStamp($data)) {
            return $data;
        }

        // Clearly not a timestamp, check if the data is a date format. dd-mm-yyyy
        $date = explode('-', $data);
        if (count($date) == 3) {
            // Cool, we have a date
            $month = self::month($date[1]);
            $year =  self::year($date[2]);
            $dt = new \DateTimeImmutable(self::monthDay($date[0], $month, $year) .
                '-' . $month .
                '-' . $year, new \DateTimeZone($timezone));
            $timestamp = $dt->getTimestamp() + 25200;

            // If we can get a timestamp from the date, return that.
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        // If it is not a unix timestamp not a date we can generate timestamp from, just get the current time!
        return time(); //now
    }

    public static function monthDay($day, $month, $year)
    {
        $maxDaysAllowed = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if ($day > $maxDaysAllowed || $day < 0) {
            // return today
            return date('j');
        }

        return (int) $day;


    }

    /**
     * @param string $timestamp
     * @param string $timezone
     * @return \DateTime
     */
    public static function getDateObjectFromUnixTimestamp(string $timestamp, string $timezone = ''): \DateTime
    {
        $d = new \DateTime(date('@' . $timestamp));
        if (self::isTimeZoneValid($timezone)) {
            $d->setTimezone(new DateTimeZone($timezone));
        }

        return $d;
    }

    /**
     * [month description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function month($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || ((int) $data) > 12 || ((int) $data) < 1 || strlen($data) > 2 || strlen($data) < 1) {
            $d = new \DateTime('now');
            return $d->format('m');
        }

        return $data;
    }

    /**
     * [hijriMonth description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function hijriMonth($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || $data > 12 || $data < 1) {
            $cs = new HijriCalendarService();
            return $cs->getCurrentIslamicMonth();
        }

        return $data;
    }

    /**
     * [year description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function year($data)
    {
        if ($data == '' || $data == null || !is_numeric($data) || strlen($data) < 1 || ((int) $data) < 1 ) {
            $d = new \DateTime('now');
            return $d->format('Y');
        }

        return $data;
    }

    /**
     * [hijriYear description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function hijriYear($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            $cs = new HijriCalendarService();
            return $cs->getCurrentIslamicYear();
        }

        return $data;
    }

    /**
     * [annual description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function annual($data)
    {
        if ($data == 'true') {
            return true;
        }

        return false;
    }

    /**
     * [isLatitudeValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isLatitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }

        return true;
    }

    /**
     * [isLongitudeValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isLongitudeValid($data)
    {
        if ($data == '' || $data == null || !is_numeric($data)) {
            return false;
        }

        return true;
    }

    /**
     * [isTimeZoneValid description]
     * @param  [type]  $data [description]
     * @return boolean       [description]
     */
    public static function isTimeZoneValid($data)
    {
        return $data !== null && Timezone::isTimeZoneValid($data);
    }

    /**
     * [isTimingsRequestValid description]
     * @param  [type]  $lat      [description]
     * @param  [type]  $lng      [description]
     * @param  [type]  $timezone [description]
     * @return boolean           [description]
     */
    public static function isTimingsRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }

    /**
     * [isCalendarRequestValid description]
     * @param  [type]  $lat      [description]
     * @param  [type]  $lng      [description]
     * @param  [type]  $timezone [description]
     * @return boolean           [description]
     */
    public static function isCalendarRequestValid($lat, $lng, $timezone)
    {
        return self::isLatitudeValid($lat) && self::isLongitudeValid($lng) && self::isTimeZoneValid($timezone);
    }

    public static function isValidAddress(string $string): bool
    {
        // Adding this here because bots are generating most of the invalid addresses
//        if (Timezone::isGoogleBot()) {
//            return false;
//        }
        $characters = ["#", "@", "<", ">", "!", "", "Ä", "\n", "\x"];
        foreach ($characters as $x) {
            if (str_contains($string, $x)) {
                return false;
            }
        }

        if (self::containsCJK($string)) {
            return false;
        }

        if (preg_match("/\p{Han}+/u", $string))
        {
            return false;
        }

        if (self::containsEmoji($string)) {
            return false;
        }

        return true;
    }

    public static function containsCJK($string) {
        $regex = '/'.implode('|', self::getCJKRanges()).'/u';
        return preg_match($regex, $string);
    }

    public static function getCJKRanges() {

        return array(
            "[\x{2E80}-\x{2EFF}]",      # CJK Radicals Supplement
            "[\x{2F00}-\x{2FDF}]",      # Kangxi Radicals
            "[\x{2FF0}-\x{2FFF}]",      # Ideographic Description Characters
            "[\x{3000}-\x{303F}]",      # CJK Symbols and Punctuation
            "[\x{3040}-\x{309F}]",      # Hiragana
            "[\x{30A0}-\x{30FF}]",      # Katakana
            "[\x{3100}-\x{312F}]",      # Bopomofo
            "[\x{3130}-\x{318F}]",      # Hangul Compatibility Jamo
            "[\x{3190}-\x{319F}]",      # Kanbun
            "[\x{31A0}-\x{31BF}]",      # Bopomofo Extended
            "[\x{31F0}-\x{31FF}]",      # Katakana Phonetic Extensions
            "[\x{3200}-\x{32FF}]",      # Enclosed CJK Letters and Months
            "[\x{3300}-\x{33FF}]",      # CJK Compatibility
            "[\x{3400}-\x{4DBF}]",      # CJK Unified Ideographs Extension A
            "[\x{4DC0}-\x{4DFF}]",      # Yijing Hexagram Symbols
            "[\x{4E00}-\x{9FFF}]",      # CJK Unified Ideographs
            "[\x{A000}-\x{A48F}]",      # Yi Syllables
            "[\x{A490}-\x{A4CF}]",      # Yi Radicals
            "[\x{AC00}-\x{D7AF}]",      # Hangul Syllables
            "[\x{F900}-\x{FAFF}]",      # CJK Compatibility Ideographs
            "[\x{FE30}-\x{FE4F}]",      # CJK Compatibility Forms
            "[\x{1D300}-\x{1D35F}]",    # Tai Xuan Jing Symbols
            "[\x{20000}-\x{2A6DF}]",    # CJK Unified Ideographs Extension B
            "[\x{2F800}-\x{2FA1F}]"     # CJK Compatibility Ideographs Supplement
        );

    }


    public static function containsEmoji($str): bool
    {
        $emoji = Emoji\detect_emoji($str);

        return count($emoji) > 0;
    }

    public static function isValidLocationPair(string $city = null, string $country = null): bool
    {
        if ($city === null || $country === null) {
            return false;
        }
        return self::isValidAddress($city) && self::isValidAddress($country);
    }


    public static function calculateClosestMethod(float $latitude, float $longitude): string|int|false
    {
        $methods = Method::getMethods();
        $distances = [];
        foreach ($methods as $method) {
            if (!in_array($method['id'], [15, 99])) {
                $distances[$method['id']] = Haversini::calculate($latitude, $longitude, $method['location']['latitude'], $method['location']['longitude']);
            }
        }

        $smallest = min($distances);

        return array_search($smallest, $distances);

    }

    public static function redirectableByDate(?string $datestring): bool
    {
        return $datestring === null || self::isUnixTimeStamp($datestring);
    }

    public static function getCalendarRedirectableUrl(bool $hijri, bool $annual, int $qyear, int $qmonth, string $endpointSuffix = ""): string
    {
        if ($annual) {
            if ($hijri) {
                return "/v1/hijriCalendar$endpointSuffix/$qyear?";
            } else {
                return "/v1/calendar$endpointSuffix/$qyear?";
            }
        } else {
            if ($hijri) {
                return "/v1/hijriCalendar$endpointSuffix/$qyear/$qmonth?";
            } else {
                return "/v1/calendar$endpointSuffix/$qyear/$qmonth?";
            }
        }
    }

    public static function calendarGetQYear(bool $hijri, HijriCalendar $hc): int
    {
        if ($hijri) {
            return $hc->getCurrentIslamicYear();
        }

        return date('Y');
    }

    public static function calendarGetQMonth(bool $hijri, HijriCalendar $hc, $adjustment): int
    {
        if ($hijri) {
            return $hc->getCurrentIslamicMonth($adjustment);
        }

        return date('n');
    }

    public static function getRedirectableDate(?string $datestring): \DateTime
    {
        if ($datestring === null) {
            return new \DateTime('now');
        } else {
            return self::getDateObjectFromUnixTimestamp($datestring);
        }
    }

    public static function isMonthValid($data): bool
    {
        if ($data == '' || $data == null || !is_numeric($data) || ((int) $data) > 12 || ((int) $data) < 1 || strlen($data) > 2 || strlen($data) < 1) {
            return false;
        }

        return true;
    }

    public static function isYearValid($data): bool
    {
        if ($data == '' || $data == null || !is_numeric($data) || strlen($data) < 1 || ((int) $data) < 1 ) {
            return false;
        }

        return true;
    }
}
