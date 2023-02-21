<?php

namespace Api\Models;

use Api\Utils\ClassMapper;
use Api\Utils\Generic;
use Api\Utils\PrayerTimesHelper;
use Api\Utils\Request as ApiRequest;
use IslamicNetwork\PrayerTimes\Method;
use Mamluk\Kipchak\Components\Http\Request;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Utils\Timezone;
use Slim\Exception\HttpBadRequestException;
use SevenEx\SDK\Geocode;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use DateTimeZone;
use DateTime;


class PrayerTimes
{
    public string $SevenExApiKey;
    public ServerRequestInterface $request;
    public string $school;
    public string $midnightMode;
    public string $latitudeAdjustmentMethod;
    public ?float $latitude;
    public ?float $longitude;
    public string $method;
    public ?string $timezone;
    public int $adjustment;
    public string $iso8601;
    public string $shafaq;
    public ?string $address;
    public ?string $city;
    public ?string $state;
    public ?string $country;
    public ?string $methodSettings;
    public MemcachedAdapter $mc;
    public array $tune;

    public function __construct(ContainerInterface $container, ServerRequestInterface $request, MemcachedAdapter $mc)
    {
        $c = $container->get('config')['kipchak.7x'];
        $this->mc = $mc;
        $this->SevenExApiKey = $c['apikey'];
        $this->request = $request;
        $this->latitude = (float) Request::getQueryParam($request, 'latitude');
        $this->longitude = (float) Request::getQueryParam($request, 'longitude');
        $this->school = ClassMapper::school(ApiRequest::school(Request::getQueryParam($request, 'school')));
        $this->midnightMode = ClassMapper::midnightMode(ApiRequest::midnightMode((int) Request::getQueryParam($request, 'midnightMode')));
        $this->latitudeAdjustmentMethod = ClassMapper::latitudeAdjustmentMethod(ApiRequest::latitudeAdjustmentMethod(Request::getQueryParam($request, 'latitudeAdjustmentMethod')));
        $this->tune = ApiRequest::tune((string) Request::getQueryParam($request, 'tune'));
        $this->adjustment = (int) Request::getQueryParam($request, 'adjustment');
        $this->iso8601 = Request::getQueryParam($request, 'iso8601') === 'true' ? \IslamicNetwork\PrayerTimes\PrayerTimes::TIME_FORMAT_ISO8601 : \IslamicNetwork\PrayerTimes\PrayerTimes::TIME_FORMAT_24H;
        $this->shafaq = ApiRequest::shafaq(Request::getQueryParam($request, 'shafaq'));
        $this->address = Request::getQueryParam($request, 'address');
        $this->city = Request::getQueryParam($request, 'city');
        $this->country = Request::getQueryParam($request, 'country');
        $this->state = Request::getQueryParam($request, 'state');
        $this->validate();
        $this->method = ClassMapper::method(ApiRequest::method(Request::getQueryParam($request, 'method'), $this->latitude, $this->longitude));
        $this->methodSettings = Request::getQueryParam($request, 'methodSettings');
        $this->timezone = $this->mc->get(md5('tz.' . $this->latitude . '.' . $this->longitude), function (ItemInterface $item) use ($request) {
            $item->expiresAfter(604800);
            return Timezone::computeTimezone($this->latitude, $this->longitude,
                Request::getQueryParam($request, 'timezonestring'), $this->SevenExApiKey);
        });

    }

    public function validate(): void
    {
        $this->validateAddress();
        $this->validateCoOrdinates();

    }

    public function validateCoOrdinates(): void
    {
        if (!Generic::isCoOrdinateAValidFormat([$this->latitude, $this->longitude])) {
            throw new HttpBadRequestException($this->request,'The geographical coordinates (latitude and longitude) that you specified or we were able to calculate from the address or city are invalid.');
        }
    }

    public function validateAddress(): void
    {
        if ($this->city !== null) {
            $this->address .= $this->city;
        }
        if ($this->state !== null) {
            $this->address .= ', ' . $this->state;
        }

        if ($this->country !== null) {
            $this->address .= ', ' . $this->country;
        }

        if ($this->address === null) {
            // This means that this is a /timings call, nothing is required.
        }

        if ($this->address !== null && ApiRequest::isValidAddress($this->address)) {
            // /timingsByAddress call. Geocode.
            $coordinates = $this->mc->get(md5('addr.' . strtolower($this->address)), function (ItemInterface $item)  {
                $item->expiresAfter(604800);
                $gc = new Geocode($this->SevenExApiKey);
                $gcode = $gc->geocode($this->address);
                if(!empty($gcode->objects)) {
                    return $gcode->objects[0]->coordinates;
                }

                return false;
            });

            if ($coordinates) {
                $this->latitude = $coordinates->latitude;
                $this->longitude = $coordinates->longitude;
            } else {
                throw new HttpBadRequestException($this->request,'Unable to geocode address.');
            }

        }
    }

    public function respond(string $datestring, string $endpoint, int $expires = 604800): array
    {
        $r = $this->mc->get(md5($endpoint . $datestring . json_encode(get_object_vars($this))),
            function (ItemInterface $item) use ($datestring, $expires) {
                $item->expiresAfter($expires);
                $timestamp = ApiRequest::time($datestring, $this->timezone);
                $d = new DateTime(date('@' . $timestamp));
                $d->setTimezone(new DateTimeZone($this->timezone));
                $pt = PrayerTimesHelper::getAndPreparePrayerTimesObject($this->request, $d, $this->method, $this->school, $this->tune, $this->adjustment, $this->shafaq);
                $timings = $pt->getTimes($d, $this->latitude, $this->longitude, null, $this->latitudeAdjustmentMethod, $this->midnightMode, $this->iso8601);
                $cs = new HijriCalendar();
                $hd = $cs->gToH($d->format('d-m-Y'), $this->adjustment);
                $date = [
                    'readable' => $d->format('d M Y'),
                    'timestamp' => $d->format('U'),
                    'hijri' => $hd['hijri'],
                    'gregorian' => $hd['gregorian']
                ];

                return [$timings, $date, $pt, $d];
            });

        return $r;

    }

    public function respondWithCalendar(int $month, int $year, bool $annual, string $endpoint, bool $hijri = false, int $expires = 604800): array
    {
        $r = $this->mc->get(md5($endpoint . $month . $year . $annual . $hijri . json_encode(get_object_vars($this))),
            function (ItemInterface $item) use ($month, $year, $annual, $hijri, $expires) {
                $item->expiresAfter($expires);
                $pt = new \IslamicNetwork\PrayerTimes\PrayerTimes($this->method, $this->school);
                $pt->setShafaq($this->shafaq);
                if ($this->method == Method::METHOD_CUSTOM) {
                    $methodSettings = ApiRequest::customMethod($this->methodSettings);
                    $customMethod = PrayerTimesHelper::createCustomMethod($methodSettings[0],
                        $methodSettings[1], $methodSettings[2]);
                    $pt->setCustomMethod($customMethod);
                    $pt->tune($this->tune[0], $this->tune[1], $this->tune[2], $this->tune[3], $this->tune[4], $this->tune[5], $this->tune[6], $this->tune[7], $this->tune[8]);
                }

                if ($hijri) {
                    if ($annual) {
                        $times = PrayerTimesHelper::calculateHijriYearPrayerTimes($this->latitude, $this->longitude,
                            $year, $this->timezone, $this->latitudeAdjustmentMethod, $pt, $this->midnightMode,
                            $this->adjustment, $this->tune, $this->iso8601);
                    } else {
                        $times = PrayerTimesHelper::calculateHijriMonthPrayerTimes($this->latitude, $this->longitude,
                            $month, $year, $this->timezone, $this->latitudeAdjustmentMethod, $pt, $this->midnightMode,
                            $this->adjustment, $this->tune, $this->iso8601);
                    }
                } else {
                    if ($annual) {
                        $times = PrayerTimesHelper::calculateYearPrayerTimes($this->latitude, $this->longitude,
                            $year, $this->timezone, $this->latitudeAdjustmentMethod, $pt, $this->midnightMode,
                            $this->adjustment, $this->tune, $this->iso8601);
                    } else {
                        $times = PrayerTimesHelper::calculateMonthPrayerTimes($this->latitude, $this->longitude,
                            $month, $year, $this->timezone, $this->latitudeAdjustmentMethod, $pt, $this->midnightMode,
                            $this->adjustment, $this->tune, $this->iso8601);
                    }
                }

                return $times;
            });

        return $r;

    }

}