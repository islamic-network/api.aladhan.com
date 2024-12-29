<?php

namespace Api\Utils;

use DateTime;
use IslamicNetwork\Calendar\Helpers\Calendar;
use IslamicNetwork\Calendar\Models\Astronomical\Diyanet;
use IslamicNetwork\Calendar\Models\Astronomical\HighJudiciaryCouncilOfSaudiArabia;
use IslamicNetwork\Calendar\Models\Astronomical\UmmAlQura;
use IslamicNetwork\Calendar\Models\Mathematical\Calculator;
use IslamicNetwork\Calendar\Types\Hijri\Date;

class HijriDate
{
    public const CALENDAR_METHOD_DIYANET = Diyanet::ID;
    public const CALENDAR_METHOD_UAQ = UmmAlQura::ID;
    public const CALENDAR_METHOD_HJCoSA = HighJudiciaryCouncilOfSaudiArabia::ID;
    public const CALENDAR_METHOD_MATHEMATICAL = Calculator::ID;


    public static function getCalendarMethods(): array
    {
        return [
            [
                'id' => self::CALENDAR_METHOD_UAQ,
                'name' => UmmAlQura::NAME,
                'description' => UmmAlQura::DESCRIPTION,
                'validity' => UmmAlQura::VALIDITY_PERIOD,

            ],
            [
                'id' => self::CALENDAR_METHOD_HJCoSA,
                'name' => HighJudiciaryCouncilOfSaudiArabia::NAME,
                'description' => HighJudiciaryCouncilOfSaudiArabia::DESCRIPTION,
                'validity' => HighJudiciaryCouncilOfSaudiArabia::VALIDITY_PERIOD,
            ],
            [
                'id' => self::CALENDAR_METHOD_MATHEMATICAL,
                'name' => Calculator::NAME,
                'description' => Calculator::DESCRIPTION,
                'validity' => Calculator::VALIDITY_PERIOD,
            ],
            [
                'id' => self::CALENDAR_METHOD_DIYANET,
                'name' => Diyanet::NAME,
                'description' => Diyanet::DESCRIPTION,
                'validity' => Diyanet::VALIDITY_PERIOD,
            ]
        ];

    }

    /**
     * validates and returns a method id
     * @param string|null $method
     * @return string
     */
    public static function calendarMethod(?string $method): string
    {
        if (in_array($method,
            [
                self::CALENDAR_METHOD_DIYANET,
                self::CALENDAR_METHOD_HJCoSA,
                self::CALENDAR_METHOD_UAQ,
                self::CALENDAR_METHOD_MATHEMATICAL
            ]
        )) {
            return $method;
        }

        // Return the default method
        return self::CALENDAR_METHOD_HJCoSA;
    }

    public static function createCalculator(string $method): HighJudiciaryCouncilOfSaudiArabia | UmmAlQura | Diyanet | Calculator
    {
        switch ($method) {
            case self::CALENDAR_METHOD_UAQ:
                return new UmmAlQura();
            case self::CALENDAR_METHOD_DIYANET:
                return new Diyanet();
            case self::CALENDAR_METHOD_MATHEMATICAL:
                return new Calculator();
            default:
                return new HighJudiciaryCouncilOfSaudiArabia();
        }
    }

    public static function isCalendarMethodAdjustable(string $method): bool
    {
        return $method === "MATHEMATICAL";
    }

    public static function sanitiseHijriCalendarMethod(string $d, string $m, string $y, string $method): string
    {
        switch ($method) {
            case self::CALENDAR_METHOD_DIYANET:
                if ($y < 1318 || $y > 1449) {
                    return self::CALENDAR_METHOD_MATHEMATICAL;
                } else {
                    return $method;
                }
            case self::CALENDAR_METHOD_UAQ:
            case self::CALENDAR_METHOD_HJCoSA:
                if ($y < 1356 || $y > 1500) {
                    return self::CALENDAR_METHOD_MATHEMATICAL;
                } else {
                    return $method;
                }
            default:
                return self::CALENDAR_METHOD_MATHEMATICAL;
        }
    }

    public static function sanitiseGregorianCalendarMethod(string $d, string $m, string $y, string $method): string
    {
        switch ($method) {
            case self::CALENDAR_METHOD_DIYANET:
                if ($y < 1900 || $y > 2028) {
                    return self::CALENDAR_METHOD_MATHEMATICAL;
                } else {
                    return $method;
                }
            case self::CALENDAR_METHOD_UAQ:
            case self::CALENDAR_METHOD_HJCoSA:
                if ($y < 1937 || $y > 2077) {
                    return self::CALENDAR_METHOD_MATHEMATICAL;
                } else {
                    return $method;
                }
            default:
                return self::CALENDAR_METHOD_MATHEMATICAL;
        }

    }

    public static function getFormattedResponse(DateTime $gd, Date $hd): array
    {
        return  [
            'hijri' =>
                [
                    'date' => ($hd->day->number < 10 ? '0' . $hd->day->number : $hd->day->number) . '-' .
                        ($hd->month->number < 10 ? '0' . $hd->month->number : $hd->month->number) . '-' .
                        $hd->year,
                    'format' => 'DD-MM-YYYY',
                    'day' => $hd->day->number,
                    'weekday' => Calendar::hijriWeekdays($gd->format('l')),
                    'month' => $hd->month,
                    'year' => $hd->year,
                    'designation' => ['abbreviated' => 'AH', 'expanded' => 'Anno Hegirae'],
                    'holidays' => $hd->holidays,
                    'method' => $hd->method,
                ],
            'gregorian' =>
                [
                    'date' => $gd->format('d-m-Y'),
                    'format' => 'DD-MM-YYYY',
                    'day' => $gd->format('d'),
                    'weekday' => ['en' => $gd->format('l')],
                    'month' => Calendar::getGregorianMonths()[(int) $gd->format('m')],
                    'year' => $gd->format('Y'),
                    'designation' => ['abbreviated' => 'AD', 'expanded' => 'Anno Domini']
                ],

        ];
    }

}