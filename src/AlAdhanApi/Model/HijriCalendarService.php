<?php

namespace AlAdhanApi\Model;

use DateTime;
use DateTimeZone;

 /**
  * Class HijriCalendarService
  * @package Model\HijriCalendarService
  */
class HijriCalendarService
{

    public $Day;
    public $Month;
    public $Year;

    /**
     *
     * @param  Float $floatNum
     * @return Integer
     */
    public function intPart($floatNum)
    {
        if ($floatNum< -0.0000001) {
            return ceil($floatNum-0.0000001);
        }
        return floor($floatNum+0.0000001);
    }

    /**
     * Extract day, month, year out of the date based on the format.
     * @param String $date
     * @param String $format
     */
    public function ConstractDayMonthYear($date, $format)
    {
        $this->Day="";
        $this->Month="";
        $this->Year="";

        $format=strtoupper($format);
        $format_Ar= str_split($format);
        $srcDate_Ar=str_split($date);

        for ($i=0; $i<count($format_Ar); $i++) {
            switch ($format_Ar[$i]) {
                case "D":
                    $this->Day.=$srcDate_Ar[$i];
                    break;
                case "M":
                    $this->Month.=$srcDate_Ar[$i];
                    break;
                case "Y":
                    $this->Year.=$srcDate_Ar[$i];
                    break;
            }
        }
    }

    /**
     * Returns Gregorian date based on Hijri date
     * @param String $date
     * @param String $format
     */
    public function HijriToGregorian($date, $format) // $date like 10121400, $format like DDMMYYYY, take date & check if its hijri then convert to gregorian date in format (DD-MM-YYYY), if it gregorian the return empty;
    {

        $this->ConstractDayMonthYear($date, $format);
        // $d=intval($this->Day)+1;
        $d=intval($this->Day);
        $m=intval($this->Month);
        $y=intval($this->Year);

        //if ($y<1700)
        //{

        $jd=$this->intPart((11*$y+3)/30)+354*$y+30*$m-$this->intPart(($m-1)/2)+$d+1948440-385;

        if ($jd> 2299160) {
            $l=$jd+68569;
            $n=$this->intPart((4*$l)/146097);
            $l=$l-$this->intPart((146097*$n+3)/4);
            $i=$this->intPart((4000*($l+1))/1461001);
            $l=$l-$this->intPart((1461*$i)/4)+31;
            $j=$this->intPart((80*$l)/2447);
            $d=$l-$this->intPart((2447*$j)/80);
            $l=$this->intPart($j/11);
            $m=$j+2-12*$l;
            $y=100*($n-49)+$i+$l;
        } else {
            $j=$jd+1402;
            $k=$this->intPart(($j-1)/1461);
            $l=$j-1461*$k;
            $n=$this->intPart(($l-1)/365)-$this->intPart($l/1461);
            $i=$l-365*$n+30;
            $j=$this->intPart((80*$i)/2447);
            $d=$i-$this->intPart((2447*$j)/80);
            $i=$this->intPart($j/11);
            $m=$j+2-12*$i;
            $y=4*$k+$n+$i-4716;
        }

        if ($d<10) {
            $d="0".$d;
        }

        if ($m<10) {
            $m="0".$m;
        }

        return $d."-".$m."-".$y;
        //}
        //else
        //	return false;
    }

    /**
     * Returns Hijri date based on Gregorian date
     * @param String $date
     * @param String $format
     */
    public function GregorianToHijri($date, $format) // $date like 10122011, $format like DDMMYYYY, take date & check if its gregorian then convert to hijri date in format (DD-MM-YYYY), if it hijri the return empty;
    {
        $this->ConstractDayMonthYear($date, $format);
        //$d=intval($this->Day)-1;
        $d=intval($this->Day);
        $m=intval($this->Month);
        $y=intval($this->Year);

        //if ($y>1700) {
        if (($y>1582)||(($y==1582)&&($m>10))||(($y==1582)&&($m==10)&&($d>14))) {
            $jd=$this->intPart((1461*($y+4800+$this->intPart(($m-14)/12)))/4)+$this->intPart((367*($m-2-12*($this->intPart(($m-14)/12))))/12)-$this->intPart((3*($this->intPart(($y+4900+$this->intPart(($m-14)/12))/100)))/4)+$d-32075;
        } else {
            $jd = 367*$y-$this->intPart((7*($y+5001+$this->intPart(($m-9)/7)))/4)+$this->intPart((275*$m)/9)+$d+1729777;
        }

            $l=$jd-1948440+10632;
            $n=$this->intPart(($l-1)/10631);
            $l=$l-10631*$n+354;
            $j=($this->intPart((10985-$l)/5316))*($this->intPart((50*$l)/17719))+($this->intPart($l/5670))*($this->intPart((43*$l)/15238));
            $l=$l-($this->intPart((30-$j)/15))*($this->intPart((17719*$j)/50))-($this->intPart($j/16))*($this->intPart((15238*$j)/43))+29;
            $m=$this->intPart((24*$l)/709);
            $d=$l-$this->intPart((709*$m)/24);
            $y=30*$n+$j-30;

        if ($d<10) {
            $d="0".$d;
        }

        if ($m<10) {
            $m="0".$m;
        }

            return $d."-".$m."-".$y;
        //} else {
        //    return false;
        //}
    }

    /**
     * Returns a list of Islamic Months
     * @return Array
     */
    public function getIslamicMonths()
    {
        return [
           1 => ['number' => 1, 'en' => 'Muḥarram', 'ar' => 'مُحَرَّم'],
           2 => ['number' => 2,'en' => 'Ṣafar', 'ar' => 'صَفَر'],
           3 => ['number' => 3,'en' => 'Rabīʿ al-awwal', 'ar' => 'رَبيع الأوّل'],
           4 => ['number' => 4,'en' => 'Rabīʿ al-thānī', 'ar' => 'رَبيع الثاني'],
           5 => ['number' => 5,'en' => 'Jumādá al-ūlá', 'ar' => 'جُمادى الأولى'],
           6 => ['number' => 6,'en' => 'Jumādá al-ākhirah', 'ar' => 'جُمادى الآخرة'],
           7 => ['number' => 7,'en' => 'Rajab', 'ar' => 'رَجَب'],
           8 => ['number' => 8,'en' => 'Shaʿbān', 'ar' => 'شَعْبان'],
           9 => ['number' => 9,'en' => 'Ramaḍān', 'ar' => 'رَمَضان'],
           10 => ['number' => 10,'en' => 'Shawwāl', 'ar' => 'شَوّال'],
           11 => ['number' => 11,'en' => 'Dhū al-Qaʿdah', 'ar' => 'ذوالقعدة'],
           12 => ['number' => 12,'en' => 'Dhū al-Ḥijjah', 'ar' => 'ذوالحجة']
        ];
    }

    /**
     * Returns a list of Gregorian months
     * @return Array
     */
    public function getGregorianMonths()
    {
        return [
           1 => ['number' => 1, 'en' => 'January'],
           2 => ['number' => 2,'en' => 'February'],
           3 => ['number' => 3,'en' => 'March'],
           4 => ['number' => 4,'en' => 'April'],
           5 => ['number' => 5,'en' => 'May'],
           6 => ['number' => 6,'en' => 'June'],
           7 => ['number' => 7,'en' => 'July'],
           8 => ['number' => 8,'en' => 'August'],
           9 => ['number' => 9,'en' => 'September'],
           10 => ['number' => 10,'en' => 'October'],
           11 => ['number' => 11,'en' => 'November'],
           12 => ['number' => 12,'en' => 'December']
        ];
    }

    /**
     * Converts given Gregorian date into AlAdhan Date Array
     * @param  String $date DD-MM-YYYY
     * @return Array
     */
    public function gToH($date, $adjustment = 0)
    {
        $date = $this->validate($date);
        if (!$date) {
            return false;
        }
        $d = $this->GregorianToHijri($date, 'DD-MM-YYYY');
        $d = $this->adjustHijriDate($d, $adjustment);
        $months = $this->getIslamicMonths();
        $monthsX = $this->getGregorianMonths();
        if ($d) {
            $dP = explode('-', $d);
            $dX = explode('-', $date);
            $month = $months[(int) $dP[1]];
            $monthX =  $monthsX[(int) $dX[1]];

            $response = [
                'hijri' =>
                [
                    'date' => $d,
                    'format' => 'DD-MM-YYYY',
                    'day' => $dP[0],
                    'weekday' => $this->hijriWeekdays(date('l', strtotime($date))),
                    'month' => $month,
                    'year' => $dP[2],
                    'designation' => ['abbreviated' => 'AH', 'expanded' => 'Anno Hegirae'],
                    'holidays' => $this->getHijriHolidays($dP[0], $month['number'])
                ],
                'gregorian' =>
                [
                    'date' => $date,
                    'format' => 'DD-MM-YYYY',
                    'day' => $dX[0],
                    'weekday' => ['en' => date('l', strtotime($date))],
                    'month' => $monthX,
                    'year' => $dX[2],
                    'designation' => ['abbreviated' => 'AD', 'expanded' => 'Anno Domini']
                ],

            ];

            return $response;
        }

        return false;
    }

    /**
     * Converts given Hijri date into AlAdhan Date Array
     * @param  String $date DD-MM-YYYY
     * @return Array
     */
    public function hToG($date, $adjustment = 0)
    {
        // Not ideal for Hijri date validation because this validates a gregorian date!
        $date = $this->validate($date);
        if (!$date) {
            return false;
        }
        $d = $this->HijriToGregorian($date, 'DD-MM-YYYY');
        $date = $this->adjustHijriDate($date, $adjustment);
        $months = $this->getGregorianMonths();
        $monthsX = $this->getIslamicMonths();
        if ($d) {
            $dP = explode('-', $d);
            $dX = explode('-', $date);
            $month = $months[(int) $dP[1]];
            $monthX =  $monthsX[(int) $dX[1]];

            $response = [
                'gregorian' =>
                    [
                    'date' => $d,
                    'format' => 'DD-MM-YYYY',
                    'day' => $dP[0],
                    'weekday' => ['en' => date('l', strtotime($d))],
                    'month' => $month,
                    'year' => $dP[2],
                    'designation' => ['abbreviated' => 'AD', 'expanded' => 'Anno Domini']
                    ],
                    'hijri' =>
                    [
                    'date' => $date,
                    'format' => 'DD-MM-YYYY',
                    'day' => $dX[0],
                    'weekday' => $this->hijriWeekdays(date('l', strtotime($d))),
                    'month' => $monthX,
                    'year' => $dX[2],
                    'designation' => ['abbreviated' => 'AH', 'expanded' => 'Anno Hegirae'],
                    'holidays' => $this->getHijriHolidays($dX[0], $monthX['number'])

                    ]
            ];

            return $response;
        }

        return false;
    }

    /**
     * Converts a given Hijri Month into AlAdhan Date Array
     * @param  Integer $m
     * @param  Integer $y
     * @return Array Array of AlAdhan date Arrays
     */
    public function getHtoGCalendar($m, $y, $adjustment = 0)
    {
        if ($m > 12) {
            $m = 12;
        }
        if ($m < 1) {
            $m = 1;
        }
        if ($y < 1) {
            $y = 1438;
        }

        $days = 30; // Islamic months have 30 or less days - always.

        $calendar = [];
        $combineCal = [];
        for ($i=1; $i<=$days; $i++) {
            $curDate = $i . '-' . $m . '-' . $y;
            $calendar = $this->hToG($curDate, $adjustment);
            if ($calendar['hijri']['month']['number'] != $m) {
                unset($calendar[$i]);
            }
            $combineCal[] = $calendar;
        }

        return $combineCal;
    }

    /**
     * Converts a given Gregorian Month into AlAdhan Date Array
     * @param  Integer $m
     * @param  Integer $y
     * @return Array Array of AlAdhan date Arrays
     */
    public function getGToHCalendar($m, $y, $adjustment = 0)
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
            $calendar = $this->gToH($curDate, $adjustment);
            if ($calendar['gregorian']['month']['number'] != $m) {
                unset($calendar[$i]);
            }
            $combineCal[] = $calendar;
        }

        return $combineCal;
    }

    /**
     * /**
     * Adjusts gregorian date by a given number of days
     * @param  String $date
     * @param  Int $days
     * @return mixed|String or Boolean
     */
    public function adjustGregorianDate($date, int $days)
    {
        try {
            $d = DateTime::createFromFormat('d-m-Y', $date);
            if ($d) {
                $d->modify("$days day");

                return $d->format('d-m-Y');
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * /**
     * Adjusts hijri date by a given number of days
     * @param  String $date
     * @param  Int $days
     * @return mixed|String or Boolean
     */
    public function adjustHijriDate($date, int $days)
    {
        $this->ConstractDayMonthYear($date, 'DD-MM-YYYY');
        $daysInMonth = 30;
        $monthsInYear = 12;

        $day = intval($this->Day);
        $month = intval($this->Month);
        $year = intval($this->Year);
        
        $day = $day + $days;

        if ($day > $daysInMonth) {
            // Adjust it and increase the month
            $day = $day - $daysInMonth;
            $month = $month + 1;
        }

        if ($day < 1) {
            $day = $day + $daysInMonth;
            $month = $month - 1;
        }

        if ($month > 12) {
            // Adjust it and increase the year
            $month = $month - $monthsInYear;
            $year = $year + 1;
        }

        if ($month < 1) {
            // Adjust it and increase the year
            $month = $month + $monthsInYear;
            $year = $year - 1;
        }

        return $day . '-' . $month . '-' . $year;
    }

    /**
     * Checks if the date is in the valid d-m-Y format
     * @param  String $string
     * @return mixed|String or Boolean
     */
    public function validate($string)
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
     * Returns an array of Holy days in the Islamic Calendar
     * @return Array
     */
    public function specialDays()
    {
        $days = [];
        $days[] = ['month' => 1, 'day' => 10, 'name' => 'Ashura'];
        $days[] = ['month' => 3, 'day' => 12, 'name' => 'Mawlid al-Nabi'];
        $days[] = ['month' => 7, 'day' => 27, 'name' => 'Lailat-ul-Miraj'];
        $days[] = ['month' => 8, 'day' => 15, 'name' => 'Lailat-ul-Bara\'at'];
        $days[] = ['month' => 9, 'day' => 1, 'name' => '1st Day of Ramadan'];
        $days[] = ['month' => 9, 'day' => 21, 'name' => 'Lailat-ul-Qadr'];
        $days[] = ['month' => 9, 'day' => 23, 'name' => 'Lailat-ul-Qadr'];
        $days[] = ['month' => 9, 'day' => 25, 'name' => 'Lailat-ul-Qadr'];
        $days[] = ['month' => 9, 'day' => 27, 'name' => 'Lailat-ul-Qadr'];
        $days[] = ['month' => 9, 'day' => 29, 'name' => 'Lailat-ul-Qadr'];
        $days[] = ['month' => 10, 'day' => 1, 'name' => 'Eid-ul-Fitr'];
        $days[] = ['month' => 12, 'day' => 8, 'name' => 'Hajj'];
        $days[] = ['month' => 12, 'day' => 9, 'name' => 'Hajj'];
        $days[] = ['month' => 12, 'day' => 9, 'name' => 'Arafa'];
        $days[] = ['month' => 12, 'day' => 10, 'name' => 'Eid-ul-Adha'];
        $days[] = ['month' => 12, 'day' => 10, 'name' => 'Hajj'];
        $days[] = ['month' => 12, 'day' => 11, 'name' => 'Hajj'];
        $days[] = ['month' => 12, 'day' => 12, 'name' => 'Hajj'];
        $days[] = ['month' => 12, 'day' => 13, 'name' => 'Hajj'];

        return $days;
    }

    /**
     * Returns holidays on a given Hijri date
     * @param  Integer $day
     * @param  Integer $month
     * @return Array
     */
    public function getHijriHolidays($day, $month)
    {
        $holydays = [];
        $day = (int) $day;
        $month = (int) $month;
        foreach ($this->specialDays() as $hol) {
            if ($hol['day'] == $day && $hol['month'] == $month) {
                $holydays[] = $hol['name'];
            }
        }

        return $holydays;
    }

    /**
     * Returns the Islamic year for today's date
     * @return Integer
     */
    public function getCurrentIslamicYear()
    {
        $date = date('d-m-Y');

        $x = $this->gToH($date);

        return $x['hijri']['year'];
    }

    /**
     * Returns the Islamic month for today's date
     * @return Integer
     */
    public function getCurrentIslamicMonth()
    {
        $date = date('d-m-Y');

        $x = $this->gToH($date);

        return $x['hijri']['month']['number'];
    }

    /**
     * Returns the Arabic names for days in a week
     * @param  Integer $gDay Optional. English names of weekdays.
     * @return Array
     */
    public function hijriWeekdays($gDay = '')
    {
        $week = [
            'Monday' => ['en' => 'Al Athnayn', 'ar' => 'الاثنين'],
            'Tuesday' => ['en' => 'Al Thalaata', 'ar' => 'الثلاثاء'],
            'Wednesday' => ['en' => 'Al Arba\'a', 'ar' => 'الاربعاء'],
            'Thursday' => ['en' => 'Al Khamees', 'ar' => 'الخميس'],
            'Friday' => ['en' => 'Al Juma\'a', 'ar' => 'الجمعة'],
            'Saturday' => ['en' => 'Al Sabt', 'ar' => 'السبت'],
            'Sunday' => ['en' => 'Al Ahad', 'ar' => 'الاحد']
        ];
        if ($gDay == '') {
            return $week;
        } else {
            return $week[$gDay];
        }
    }

    /**
     * Returns the next holiday in the Islamic Calendar
     * @param  Integer $days Number of days forward to search in
     * @return Array
     */
    public function nextHijriHoliday($days = 360)
    {
        $todayTimestamp = time();

        for ($i = 0; $i <= $days; $i++) {
            $today = date('d-m-Y', $todayTimestamp);
            // Get Hijri Date
            $hijriDate = $this->gToH($today);
            if (!empty($hijriDate['hijri']['holidays'])) {
                return $hijriDate;
            }

            $todayTimestamp = $todayTimestamp + (1*60*60*24);
        }

        return false;
    }

    /**
     * Gets Hijri Year for the Month of Ramadan based on a Gregorian Year
     * @param  Integer $gYear Gregorian Year - Example: 2012
     * @return Integer Corresponding Hijri year for gYear for the Hijri Month 9 (Ramadan)
     */
    public function getIslamicYearFromGregorianForRamadan($gYear)
    {
        $y = (int) $gYear;
        $date = $this->gToH("01-01-$y");
        $iM = $date['hijri']['month']['number'];
        if ($iM < 9) {
            // Get the date for ramadan in this islamic year
            $iY = $date['hijri']['year'];
            $newDate = $this->hToG("01-09-$iY");
            ;
            return $newDate['hijri']['year'];
        }
        if ($iM > 9) {
            // Get the date for ramadan in this islamic year
            $iY = $date['hijri']['year'] + 1;
            $newDate = $this->hToG("01-09-$iY");
            ;
            return $newDate['hijri']['year'];
        }

        return $date['hijri']['year'];
    }
}
