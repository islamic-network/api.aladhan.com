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
        $dateParts = explode('-', $date);
        $cm = HijriDate::sanitiseGregorianCalendarMethod($dateParts[0], $dateParts[1], $dateParts[2], $cm);
        $calculator = HijriDate::createCalculator($cm);
        if (HijriDate::isCalendarMethodAdjustable($cm)) {
            $d = $calculator->gToH($date, $adjustment);
        } else {
            $d = $calculator->gToH($date);
        }
        $gd = DateTime::createFromFormat('d-m-Y', $date);

        HijriDate::addLailatulRaghaib($d, $gd);

        return HijriDate::getFormattedResponse($gd, $d);
    }

    /**
     * @param string $date Hijri date dd-mm-yyyy
     * @param string $cm Calendar Method
     * @param int $adjustment
     * @return array|false
     */
    public function hToG($date, $cm, $adjustment = 0, $calendarMode = false): array | false
    {

        // Not ideal for Hijri date validation because this validates a gregorian date!
        $hdstring = $this->validateHijri($date);
        if (!$hdstring) {
            return false;
        }

        $hdstringParts = explode('-', $hdstring);
        $cm = HijriDate::sanitiseHijriCalendarMethod($hdstringParts[0], $hdstringParts[1], $hdstringParts[2], $cm);
        $calculator = HijriDate::createCalculator($cm);
        if (HijriDate::isCalendarMethodAdjustable($cm)) {
            $gd = $calculator->hToG($hdstring, $adjustment);
            $hd = $calculator->gToH($gd->format('d-m-Y'), $adjustment);
        } else {
            if ($calendarMode) {
                $gd = $calculator->hToG($hdstring, $adjustment);
            } else {
                $gd = $calculator->hToG($hdstring);
            }
            $hd = $calculator->gToH($gd->format('d-m-Y'));
        }
        if (!$calendarMode) {
            $var = 3;
            // If the date is not adjusted, check if $hdstring contained the first of a month and if you actually get the first with the conversion.
            // If not, force the result with an adjustment.
            for ($i = 1; $i < $var; $i++) {
                if ((int) $hdstringParts[0] !== $hd->day->number && $adjustment === 0 && $hd->day->number > 1) {
                    // Recalculate with a -1
                    $gd = $calculator->hToG($hdstring, -$i);
                    $hd = $calculator->gToH($gd->format('d-m-Y'));
                    if ($hd->day->number === (int) $hdstringParts[0]) {
                        break;
                    }
                    // Recalculate with a +1
                    $gd = $calculator->hToG($hdstring, $i);
                    $hd = $calculator->gToH($gd->format('d-m-Y'));
                    if ($hd->day->number === (int) $hdstringParts[0]) {
                        break;
                    }
                }
            }
        }

        HijriDate::addLailatulRaghaib($hd, $gd);

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
        for ($i = 1; $i <= $days; $i++) {
            $curDate = $i . '-' . $m . '-' . $y;
            $result = $this->hToG($curDate, $cm, $adjustment, true);
            if ($i === 1) {
                // Check the returned hijri date. Consider moving this up to the hTG function itself to even correct the single date calculation.
                $firstDay = ($result['hijri']['day']);
                if ($firstDay > 1) {
                    $var = 3;
                    for ($i = 1; $i < $var; $i++) {
                        // The hijri to julian calc is off by a day in this case because it is not astronomical, let's go back a day and compute again.
                        $resultM = $this->hToG($curDate, $cm, -$i);
                        if ($resultM['hijri']['day'] === 1) {
                            break;
                        }
                        $resultM = $this->hToG($curDate, $cm, $i);
                        if ($resultM['hijri']['day'] === 1) {
                            break;
                        }
                    }
                    $calendar[] = $resultM;
                    $calendar[] = $result;
                } else {
                    $calendar[] = $result;
                }
            } else {
                $calendar[] = $result;
            }
        }

        return $calendar;
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
