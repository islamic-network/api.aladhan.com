<?php

namespace Api\Models;

use Api\Utils\HijriDate;
use DateTime;
use Exception;
use IslamicNetwork\Calendar\Helpers\Calendar;

class HijriCalendar
{

    /**
     * @param string $date Gregorian Date dd-mm-yyyy
     * @param string $cm Calendar Method
     * @param int $adjustment
     * @return array|false
     */
    public function gToH($date, $cm, $adjustment = 0): array|false
    {
        $date = $this->validate($date);
        if (!$date) {
            return false;
        }

        $calculator = HijriDate::createCalculator($cm);
        if (HijriDate::isCalendarMethodAdjustable($cm)) {
            $d = $calculator->gToH($date, $adjustment);
        } else {
            $d = $calculator->gToH($date);
        }

        return HijriDate::getFormattedResponse(DateTime::createFromFormat('d-m-Y', $date), $d);
    }

    /**
     * @param string $date Hijri date dd-mm-yyyy
     * @param string $cm Calendar Method
     * @param int $adjustment
     * @return array|false
     */
    public function hToG($date, $cm, $adjustment = 0): array | false
    {
        // Not ideal for Hijri date validation because this validates a gregorian date!
        $hdstring = $this->validateHijri($date);
        if (!$date) {
            return false;
        }

        $calculator = HijriDate::createCalculator($cm);
        if (HijriDate::isCalendarMethodAdjustable($cm)) {
            $gd = $calculator->hToG($hdstring, $adjustment);
            $hd = $calculator->gToH($hdstring, $adjustment);
        } else {
            $gd = $calculator->hToG($hdstring);
            $hd = $calculator->gToH($gd->format('d-m-Y'));
        }

        return HijriDate::getFormattedResponse($gd, $hd);
    }

    /**
     * @param $m
     * @param $y
     * @param $cm
     * @param $adjustment
     * @return array
     * @throws \Exception
     */
    public function getHtoGCalendar($m, $y, $cm, $adjustment = 0): array
    {
        if ($m > 12) {
            $m = 12;
        }
        if ($m < 1) {
            $m = 1;
        }
        if ($y < 1) {
            $y = 1445;
        }

        $x = $this->hToG(7 . '-' . $m . '-' . $y, $cm, $adjustment);

        $days = $x['hijri']['month']->days;

        $calendar = [];
        $combineCal = [];
        for ($i = 1; $i <= $days; $i++) {
            $curDate = $i . '-' . $m . '-' . $y;
            $calendar = $this->hToG($curDate, $cm, $adjustment);
            if ($calendar !== false) {
                if ($calendar['hijri']['month']->number != $m) {
                    unset($calendar[$i]);
                }
                $combineCal[] = $calendar;
            }
        }

        return $combineCal;
    }

    /**
     * @param $m
     * @param $y
     * @param $cm
     * @param $adjustment
     * @return array
     */
    public function getGToHCalendar($m, $y, $cm, $adjustment = 0): array
    {
        if ($m > 12) {
            $m = 12;
        }
        if ($m < 1) {
            $m = 1;
        }
        if ($y < 1000) {
            $y = date('Y');
        }

        $days = cal_days_in_month(CAL_GREGORIAN, $m, $y);

        $calendar = [];
        $combineCal = [];
        for ($i=1; $i<=$days; $i++) {
            $curDate = $i . '-' . $m . '-' . $y;
            $calendar = $this->gToH($curDate, $cm, $adjustment);
            if ($calendar['gregorian']['month']['number'] != $m) {
                unset($calendar[$i]);
            }
            $combineCal[] = $calendar;
        }

        return $combineCal;
    }

    /**
     * @param string $string
     * @return string|false
     */
    public function validateHijri(string $string): string | false
    {
        try {
            $d = DateTime::createFromFormat('d-m-Y', $string);
            if ($d) {
                // return $d->format('d-m-Y');
                $parts = explode('-', $string);
                $day = $parts[0];
                $month = $parts[1];
                $year = $parts[2];
                if (strlen($day) === 1) {
                   $day = '0' . $day; 
                }
                if (strlen($month) === 1) {
                   $month = '0' . $month; 
                }

               return $day . '-' . $month . '-' . $year; 
                
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $string
     * @return string|false
     */
    public function validate(string $string): string | false
    {
        try {
            $d = DateTime::createFromFormat('d-m-Y', $string);
            if ($d) {
                return $d->format('d-m-Y');
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $cm
     * @param int|null $year
     * @param int|null $adjustment
     * @return array
     * @throws Exception
     */
    public function getIslamicHolidaysByHijriYear(string $cm, int $year = null, int $adjustment = null): array
    {
        $holidays = [];
        $year = $year === null ? $this->getCurrentIslamicYear() : $year;
        $adjustment = $adjustment === null ? 0 : $adjustment;
        foreach (Calendar::specialDays() as $day) {
            $holidays[] = $this->hToG($day['day'] . '-' . $day['month'] . '-' . $year, $cm, $adjustment);
        }

        return $holidays;
    }

    /**
     * @return int
     */
    public function getCurrentIslamicYear(): int
    {
        $date = date('d-m-Y');

        $x = $this->gToH($date, HijriDate::CALENDAR_METHOD_HJCoSA);

        return (int) $x['hijri']['year'];
    }

    /**
     * @param string $cm
     * @param int $adjustment
     * @return int
     */
    public function getCurrentIslamicMonth(string $cm, $adjustment = 0): int
    {
        $date = date('d-m-Y');

        $x = $this->gToH($date, $cm, $adjustment);

        return (int) $x['hijri']['month']->number;
    }


    /**
     * @param string $cm
     * @param int $days
     * @param int $adjustment
     * @return array|false
     */
    public function nextHijriHoliday(string $cm, $days = 360, $adjustment = 0): array | false
    {
        $todayTimestamp = time();

        for ($i = 0; $i <= $days; $i++) {
            $today = date('d-m-Y', $todayTimestamp);
            // Get Hijri Date
            $hijriDate = $this->gToH($today, $cm, $adjustment);
            if (!empty($hijriDate['hijri']['holidays'])) {
                return $hijriDate;
            }

            $todayTimestamp = $todayTimestamp + (1 * 60 * 60 * 24);
        }

        return false;
    }

    /**
     * @param $gYear
     * @return int
     * @throws \Exception
     */
    public function getIslamicYearFromGregorianForRamadan($gYear): int
    {
        $y = (int) $gYear;
        $date = $this->gToH("01-01-$y", HijriDate::CALENDAR_METHOD_HJCoSA);
        $iM = $date['hijri']['month']->number;
        if ($iM < 9) {
            // Get the date for ramadan in this islamic year
            $iY = $date['hijri']['year'];
            $newDate = $this->hToG("01-09-$iY", HijriDate::CALENDAR_METHOD_HJCoSA);

            return (int) $newDate['hijri']['year'];
        }
        if ($iM > 9) {
            // Get the date for ramadan in this islamic year
            $iY = $date['hijri']['year'] + 1;
            $newDate = $this->hToG("01-09-$iY", HijriDate::CALENDAR_METHOD_HJCoSA);

            return (int)$newDate['hijri']['year'];
        }

        return (int) $date['hijri']['year'];
    }
}
