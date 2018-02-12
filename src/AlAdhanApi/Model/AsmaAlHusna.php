<?php
namespace AlAdhanApi\Model;

/**
 * Class AsmaAlHusna
 * @package Model\AsmaAlHusna
 */
class AsmaAlHusna
{
    /**
     * The Asmaa Al Husnaa
     * @var Array
     */
    private static $names = [
        1 => [
        'name' => 'الرَّحْمَنُ',
        'transliteration' => 'Ar Rahmaan',
        'number' => 1,
        'en' => [
            'meaning' => 'The Beneficent'
            ]
        ],
        2 => [
        'name' => 'الرَّحِيمُ',
        'transliteration' => 'Ar Raheem',
        'number' => 2,
        'en' => [
            'meaning' => 'The Merciful',
            ]
        ],
        3 => [
        'name' => 'الْمَلِكُ',
        'transliteration' => 'Al Malik',
        'number' => 3,
        'en' => [
            'meaning' => 'The King / Eternal Lord',
            ]
        ],
        4 => [
        'name' => 'الْقُدُّوسُ',
        'transliteration' => 'Al Quddus',
        'number' => 4,
        'en' => [
            'meaning' => 'The Purest',
            ]
        ],
        5 => [
        'name' => 'السَّلاَمُ',
        'transliteration' => 'As Salaam',
        'number' => 5,
        'en' => [
            'meaning' => 'The Source of Peace'
            ]
        ],
        6 => [
        'name' => 'الْمُؤْمِنُ',
        'transliteration' => 'Al Mu\'min',
        'number' => 6,
        'en' => [
            'meaning' => 'The inspirer of faith'
            ]
        ],
        7 => [
        'name' => 'الْمُهَيْمِنُ',
        'transliteration' => 'Al Muhaymin',
        'number' => 7,
        'en' => [
            'meaning' => 'The Guardian'
            ]
        ],
        8 => [
        'name' => 'الْعَزِيزُ',
        'transliteration' => 'Al Azeez',
        'number' => 8,
        'en' => [
            'meaning' => 'The Precious / The Most Mighty'
            ]
        ],
        9 => [
        'name' => 'الْجَبَّارُ',
        'transliteration' => 'Al Jabbaar',
        'number' => 9,
        'en' => [
            'meaning' => 'The Compeller'
            ]
        ],
        10 => [
        'name' => 'الْمُتَكَبِّرُ',
        'transliteration' => 'Al Mutakabbir',
        'number' => 10,
        'en' => [
            'meaning' => 'The Greatest'
            ]
        ],
        11 => [
        'name' => 'الْخَالِقُ',
        'transliteration' => 'Al Khaaliq',
        'number' => 11,
        'en' => [
            'meaning' => 'The Creator'
            ]
        ],
        12 => [
        'name' => 'الْبَارِئُ',
        'transliteration' => 'Al Baari',
        'number' => 12,
        'en' => [
            'meaning' => 'The Maker of Order'
            ]
        ],
        13 => [
        'name' => 'الْمُصَوِّرُ',
        'transliteration' => 'Al Musawwir',
        'number' => 13,
        'en' => [
            'meaning' => 'The Shaper of Beauty'
            ]
        ],
        14 => [
        'name' => 'الْغَفَّارُ',
        'transliteration' => 'Al Ghaffaar',
        'number' => 14,
        'en' => [
            'meaning' => 'The Forgiving'
            ]
        ],
        15 => [
        'name' => 'الْقَهَّارُ',
        'transliteration' => 'Al Qahhaar',
        'number' => 15,
        'en' => [
            'meaning' => 'The Subduer'
            ]
        ],
        16 => [
        'name' => 'الْوَهَّابُ',
        'transliteration' => 'Al Wahhaab',
        'number' => 16,
        'en' => [
            'meaning' => 'The Giver of All'
            ]
        ],
        17 => [
        'name' => 'الرَّزَّاقُ',
        'transliteration' => 'Ar Razzaaq',
        'number' => 17,
        'en' => [
            'meaning' => 'The Sustainer'
            ]
        ],
        18 => [
        'name' => 'الْفَتَّاحُ',
        'transliteration' => 'Al Fattaah',
        'number' => 18,
        'en' => [
            'meaning' => 'The Opener'
            ]
        ],
        19 => [
        'name' => 'اَلْعَلِيْمُ',
        'transliteration' => 'Al \'Aleem',
        'number' => 19,
        'en' => [
            'meaning' => 'The Knower of all'
            ]
        ],
        20 => [
        'name' => 'الْقَابِضُ',
        'transliteration' => 'Al Qaabid',
        'number' => 20,
        'en' => [
            'meaning' => 'The Constrictor'
            ]
        ],
        21 => [
        'name' => 'الْبَاسِطُ',
        'transliteration' => 'Al Baasit',
        'number' => 21,
        'en' => [
            'meaning' => 'The Reliever'
            ]
        ],
        22 => [
        'name' => 'الْخَافِضُ',
        'transliteration' => 'Al Khaafid',
        'number' => 22,
        'en' => [
            'meaning' => 'The Abaser'
            ]
        ],
        23 => [
        'name' => 'الرَّافِعُ',
        'transliteration' => 'Ar Raafi\'',
        'number' => 23,
        'en' => [
            'meaning' => 'The Exalter'
            ]
        ],
        24 => [
        'name' => 'الْمُعِزُّ',
        'transliteration' => 'Al Mu\'iz',
        'number' => 24,
        'en' => [
            'meaning' => 'The Bestower of Honour'
            ]
        ],
        25 => [
        'name' => 'المُذِلُّ',
        'transliteration' => 'Al Mudhil',
        'number' => 25,
        'en' => [
            'meaning' => 'The Humiliator'
            ]
        ],
        26 => [
        'name' => 'السَّمِيعُ',
        'transliteration' => 'As Samee\'',
        'number' => 26,
        'en' => [
            'meaning' => 'The Hearer of all'
            ]
        ],
        27 => [
        'name' => 'الْبَصِيرُ',
        'transliteration' => 'Al Baseer',
        'number' => 27,
        'en' => [
            'meaning' => 'The Seer of all'
            ]
        ],
        28 => [
        'name' => 'الْحَكَمُ',
        'transliteration' => 'Al Hakam',
        'number' => 28,
        'en' => [
            'meaning' => 'The Judge'
            ]
        ],
        29 => [
        'name' => 'الْعَدْلُ',
        'transliteration' => 'Al \'Adl',
        'number' => 29,
        'en' => [
            'meaning' => 'The Just'
            ]
        ],
        30 => [
        'name' => 'اللَّطِيفُ',
        'transliteration' => 'Al Lateef',
        'number' => 30,
        'en' => [
            'meaning' => 'The Subtle One'
            ]
        ],
        31 => [
        'name' => 'الْخَبِيرُ',
        'transliteration' => 'Al Khabeer',
        'number' => 31,
        'en' => [
            'meaning' => 'The All Aware'
            ]
        ],
        32 => [
        'name' => 'الْحَلِيمُ',
        'transliteration' => 'Al Haleem',
        'number' => 32,
        'en' => [
            'meaning' => 'The Forebearing'
            ]
        ],
        33 => [
        'name' => 'الْعَظِيمُ',
        'transliteration' => 'Al \'Azeem',
        'number' => 33,
        'en' => [
            'meaning' => 'The Maginificent',
            ]
        ],
        34 => [
        'name' => 'الْغَفُورُ',
        'transliteration' => 'Al Ghafoor',
        'number' => 34,
        'en' => [
            'meaning' => 'The Great Forgiver',
            ]
        ],
        35 => [
        'name' => 'الشَّكُورُ',
        'transliteration' => 'Ash Shakoor',
        'number' => 35,
        'en' => [
            'meaning' => 'The Rewarder of Thankfulness',
            ]
        ],
        36 => [
        'name' => 'الْعَلِيُّ',
        'transliteration' => 'Al \'Aliyy',
        'number' => 36,
        'en' => [
            'meaning' => 'The Highest',
            ]
        ],
        37 => [
        'name' => 'الْكَبِيرُ',
        'transliteration' => 'Al Kabeer',
        'number' => 37,
        'en' => [
            'meaning' => 'The Greatest',
            ]
        ],
        38 => [
        'name' => 'الْحَفِيظُ',
        'transliteration' => 'Al Hafeez',
        'number' => 38,
        'en' => [
            'meaning' => 'The Preserver',
            ]
        ],
        39 => [
        'name' => 'المُقيِت',
        'transliteration' => 'Al Muqeet',
        'number' => 39,
        'en' => [
            'meaning' => 'The Nourisher',
            ]
        ],
        40 => [
        'name' => 'الْحسِيبُ',
        'transliteration' => 'Al Haseeb',
        'number' => 40,
        'en' => [
            'meaning' => 'The Reckoner',
            ]
        ],
        41 => [
        'name' => 'الْجَلِيلُ',
        'transliteration' => 'Al Jaleel',
        'number' => 41,
        'en' => [
            'meaning' => 'The Majestic',
            ]
        ],
        42 => [
        'name' => 'الْكَرِيمُ',
        'transliteration' => 'Al Kareem',
        'number' => 42,
        'en' => [
            'meaning' => 'The Generous',
            ]
        ],
        43 => [
        'name' => 'الرَّقِيبُ',
        'transliteration' => 'Ar Raqeeb',
        'number' => 43,
        'en' => [
            'meaning' => 'The Watchful One',
            ]
        ],
        44 => [
        'name' => 'الْمُجِيبُ',
        'transliteration' => 'Al Mujeeb ',
        'number' => 44,
        'en' => [
            'meaning' => 'The Responder to Prayer',
            ]
        ],
        45 => [
        'name' => 'الْوَاسِعُ',
        'transliteration' => 'Al Waasi\'',
        'number' => 45,
        'en' => [
            'meaning' => 'The All Comprehending',
            ]
        ],
        46 => [
        'name' => 'الْحَكِيمُ',
        'transliteration' => 'Al Hakeem',
        'number' => 46,
        'en' => [
            'meaning' => 'The Perfectly Wise',
            ]
        ],
        47 => [
        'name' => 'الْوَدُودُ',
        'transliteration' => 'Al Wudood',
        'number' => 47,
        'en' => [
            'meaning' => 'The Loving One',
            ]
        ],
        48 => [
        'name' => 'الْمَجِيدُ',
        'transliteration' => 'Al Majeed',
        'number' => 48,
        'en' => [
            'meaning' => 'The Most Glorious One',
            ]
        ],
        49 => [
        'name' => 'الْبَاعِثُ',
        'transliteration' => 'Al Baa\'ith',
        'number' => 49,
        'en' => [
            'meaning' => 'The Resurrector',
            ]
        ],
        50 => [
        'name' => 'الشَّهِيدُ',
        'transliteration' => 'Ash Shaheed',
        'number' => 50,
        'en' => [
            'meaning' => 'The Witness',
            ]
        ],
        51 => [
        'name' => 'الْحَقُّ',
        'transliteration' => 'Al Haqq',
        'number' => 51,
        'en' => [
            'meaning' => 'The Truth',
            ]
        ],
        52 => [
        'name' => 'الْوَكِيلُ',
        'transliteration' => 'Al Wakeel',
        'number' => 52,
        'en' => [
            'meaning' => 'The Trustee',
            ]
        ],
        53 => [
        'name' => 'الْقَوِيُّ',
        'transliteration' => 'Al Qawiyy',
        'number' => 53,
        'en' => [
            'meaning' => 'The Possessor of all strength',
            ]
        ],
        54 => [
        'name' => 'الْمَتِينُ',
        'transliteration' => 'Al Mateen',
        'number' => 54,
        'en' => [
            'meaning' => 'The Forceful',
            ]
        ],
        55 => [
        'name' => 'الْوَلِيُّ',
        'transliteration' => 'Al Waliyy',
        'number' => 55,
        'en' => [
            'meaning' => 'The Protector',
            ]
        ],
        56 => [
        'name' => 'الْحَمِيدُ',
        'transliteration' => 'Al Hameed',
        'number' => 56,
        'en' => [
            'meaning' => 'The Praised',
            ]
        ],
        57 => [
        'name' => 'الْمُحْصِي',
        'transliteration' => 'Al Muhsi',
        'number' => 57,
        'en' => [
            'meaning' => 'The Appraiser',
            ]
        ],
        58 => [
        'name' => 'الْمُبْدِئُ',
        'transliteration' => 'Al Mubdi',
        'number' => 58,
        'en' => [
            'meaning' => 'The Originator',
            ]
        ],
        59 => [
        'name' => 'الْمُعِيدُ',
        'transliteration' => 'Al Mu\'eed',
        'number' => 59,
        'en' => [
            'meaning' => 'The Restorer',
            ]
        ],
        60 => [
        'name' => 'الْمُحْيِي',
        'transliteration' => 'Al Muhiy',
        'number' => 60,
        'en' => [
            'meaning' => 'The Giver of life',
            ]
        ],
        61 => [
        'name' => 'اَلْمُمِيتُ',
        'transliteration' => 'Al Mumeet',
        'number' => 61,
        'en' => [
            'meaning' => 'The Taker of life',
            ]
        ],
        62 => [
        'name' => 'الْحَيُّ',
        'transliteration' => 'Al Haiyy',
        'number' => 62,
        'en' => [
            'meaning' => 'The Ever Living',
            ]
        ],
        63 => [
        'name' => 'الْقَيُّومُ',
        'transliteration' => 'Al Qayyoom',
        'number' => 63,
        'en' => [
            'meaning' => 'The Self Existing',
            ]
        ],
        64 => [
        'name' => 'الْوَاجِدُ',
        'transliteration' => 'Al Waajid',
        'number' => 64,
        'en' => [
            'meaning' => 'The Finder',
            ]
        ],
        65 => [
        'name' => 'الْمَاجِدُ',
        'transliteration' => 'Al Maajid',
        'number' => 65,
        'en' => [
            'meaning' => 'The Glorious',
            ]
        ],
        66 => [
        'name' => 'الْواحِدُ',
        'transliteration' => 'Al Waahid',
        'number' => 66,
        'en' => [
            'meaning' => 'The Only One',
            ]
        ],
        67 => [
        'name' => 'اَلاَحَدُ',
        'transliteration' => 'Al Ahad',
        'number' => 67,
        'en' => [
            'meaning' => 'The One',
            ]
        ],
        68 => [
        'name' => 'الصَّمَدُ',
        'transliteration' => 'As Samad',
        'number' => 68,
        'en' => [
            'meaning' => 'The Supreme Provider',
            ]
        ],
        69 => [
        'name' => 'الْقَادِرُ',
        'transliteration' => 'Al Qaadir',
        'number' => 69,
        'en' => [
            'meaning' => 'The Powerful',
            ]
        ],
        70 => [
        'name' => 'الْمُقْتَدِرُ',
        'transliteration' => 'Al Muqtadir',
        'number' => 70,
        'en' => [
            'meaning' => 'The Creator of all power',
            ]
        ],
        71 => [
        'name' => 'الْمُقَدِّمُ',
        'transliteration' => 'Al Muqaddim',
        'number' => 71,
        'en' => [
            'meaning' => 'The Expediter',
            ]
        ],
        72 => [
        'name' => 'الْمُؤَخِّرُ',
        'transliteration' => 'Al Mu’akhir',
        'number' => 72,
        'en' => [
            'meaning' => 'The Delayer',
            ]
        ],
        73 => [
        'name' => 'الأوَّلُ',
        'transliteration' => 'Al Awwal',
        'number' => 73,
        'en' => [
            'meaning' => 'The First',
            ]
        ],
        74 => [
        'name' => 'الآخِرُ',
        'transliteration' => 'Al Aakhir',
        'number' => 74,
        'en' => [
            'meaning' => 'The Last',
            ]
        ],
        75 => [
        'name' => 'الظَّاهِرُ',
        'transliteration' => 'Az Zaahir',
        'number' => 75,
        'en' => [
            'meaning' => 'The Manifest',
            ]
        ],
        76 => [
        'name' => 'الْبَاطِنُ',
        'transliteration' => 'Al Baatin',
        'number' => 76,
        'en' => [
            'meaning' => 'The Hidden',
            ]
        ],
        77 => [
        'name' => 'الْوَالِي',
        'transliteration' => 'Al Waali',
        'number' => 77,
        'en' => [
            'meaning' => 'The Governor',
            ]
        ],
        78 => [
        'name' => 'الْمُتَعَالِي',
        'transliteration' => 'Al Muta’ali',
        'number' => 78,
        'en' => [
            'meaning' => 'The Supreme One',
            ]
        ],
        79 => [
        'name' => 'الْبَرُّ',
        'transliteration' => 'Al Barr',
        'number' => 79,
        'en' => [
            'meaning' => 'The Doer of Good',
            ]
        ],
        80 => [
        'name' => 'التَّوَابُ',
        'transliteration' => 'At Tawwaab',
        'number' => 80,
        'en' => [
            'meaning' => 'The Guide to Repentence',
            ]
        ],
        81 => [
        'name' => 'الْمُنْتَقِمُ',
        'transliteration' => 'Al Muntaqim',
        'number' => 81,
        'en' => [
            'meaning' => 'The Avenger',
            ]
        ],
        82 => [
        'name' => 'العَفُوُّ',
        'transliteration' => 'Al Afuww',
        'number' => 82,
        'en' => [
            'meaning' => 'The Forgiver',
            ]
        ],
        83 => [
        'name' => 'الرَّؤُوفُ',
        'transliteration' => 'Ar Ra’oof',
        'number' => 83,
        'en' => [
            'meaning' => 'The Clement',
            ]
        ],
        84 => [
        'name' =>
        'مَالِكُ الْمُلْكِ',
        'transliteration' => 'Maalik Ul Mulk',
        'number' => 84,
        'en' => [
            'meaning' => 'The Owner / Soverign of All',
            ]
        ],
        85 => [
        'name' =>
        'ذُوالْجَلاَلِ وَالإكْرَامِ',
        'transliteration' => 'Dhu Al Jalaali Wa Al Ikraam',
        'number' => 85,
        'en' => [
            'meaning' => 'Possessor of Majesty and Bounty',
            ]
        ],
        86 => [
        'name' => 'الْمُقْسِطُ',
        'transliteration' => 'Al Muqsit',
        'number' => 86,
        'en' => [
            'meaning' => 'The Equitable One',
            ]
        ],
        87 => [
        'name' => 'الْجَامِعُ',
        'transliteration' => 'Al Jaami\'',
        'number' => 87,
        'en' => [
            'meaning' => 'The Gatherer',
            ]
        ],
        88 => [
        'name' => 'الْغَنِيُّ',
        'transliteration' => 'Al Ghaniyy',
        'number' => 88,
        'en' => [
            'meaning' => 'The Rich One',
            ]
        ],
        89 => [
        'name' => 'الْمُغْنِي',
        'transliteration' => 'Al Mughi',
        'number' => 89,
        'en' => [
            'meaning' => 'The Enricher',
            ]
        ],
        90 => [
        'name' => 'اَلْمَانِعُ',
        'transliteration' => 'Al Maani\'',
        'number' => 90,
        'en' => [
            'meaning' => 'The Preventer of harm',
            ]
        ],
        91 => [
        'name' => 'الضَّارَّ',
        'transliteration' => 'Ad Daaarr',
        'number' => 91,
        'en' => [
            'meaning' => 'The Creator of the harmful',
            ]
        ],
        92 => [
        'name' => 'النَّافِعُ',
        'transliteration' => 'An Naafi’',
        'number' => 92,
        'en' => [
            'meaning' => 'The Bestower of Benefits',
            ]
        ],
        93 => [
        'name' => 'النُّورُ',
        'transliteration' => 'An Noor',
        'number' => 93,
        'en' => [
            'meaning' => 'The Light',
            ]
        ],
        94 => [
        'name' => 'الْهَادِي',
        'transliteration' => 'Al Haadi',
        'number' => 94,
        'en' => [
            'meaning' => 'The Guider',
            ]
        ],
        95 => [
        'name' => 'الْبَدِيعُ',
        'transliteration' => 'Al Badi\'',
        'number' => 95,
        'en' => [
            'meaning' => 'The Originator',
            ]
        ],
        96 => [
        'name' => 'اَلْبَاقِي',
        'transliteration' => 'Al Baaqi',
        'number' => 96,
        'en' => [
            'meaning' => 'The Everlasting One',
            ]
        ],
        97 => [
        'name' => 'الْوَارِثُ',
        'transliteration' => 'Al Waarith',
        'number' => 97,
        'en' => [
            'meaning' => 'The Inhertior',
            ]
        ],
        98 => [
        'name' => 'الرَّشِيدُ',
        'transliteration' => 'Ar Rasheed',
        'number' => 98,
        'en' => [
            'meaning' => 'The Most Righteous Guide',
            ]
        ],
        99 => [
        'name' => 'الصَّبُورُ',
        'transliteration' => 'As Saboor',
        'number' => 99,
        'en' => [
            'meaning' => 'The Patient One',
            ]
        ],

    ];

    /**
     * [FORMAT_JSON description]
     * @var string
     */
    const FORMAT_JSON = 'json';

    /**
     * [FORMAT_ARRAY description]
     * @var string
     */
    const FORMAT_ARRAY = 'array';

    /**
     * Gets one or multiple names
     * @param  Integer $number
     * @param  $format Array or JSON as defined in the constants above
     * @return Array
     */
    public static function get($number = null, $format = self::FORMAT_ARRAY)
    {
        $selected = self::extract($number);

        if ($format == self::FORMAT_JSON) {
            return self::getJSON($selected);
        }

        return $selected;
    }

    /**
     * Gets one or multiple names
     * @param  Integer $number
     * @param  $format Array or JSON as defined in the constants above
     * @return Array
     */
    private static function extract($number)
    {
        if ($number === null) {
            return array_values(self::$names);
        }

        if (self::isValidNumber($number)) {
            return self::extractFromInt($number);
            ;
        }

        if (is_array($number)) {
            $selected = [];
            foreach ($number as $no) {
                if (self::isValidNumber($no)) {
                    $selected[] = self::$names[$no];
                }
            }

            return $selected;
        }
    }

    /**
     * Checks if the number of the Name is valid
     * @param  Integer  $number Between 1 and 99 inclusive
     * @return boolean         [description]
     */
    private static function isValidNumber($number)
    {
        if (is_int($number) && $number > 0 && $number < 100) {
            return true;
        }

        return false;
    }

    /**
     * Extracts a Name based on the number
     * @param  Integer $number
     * @return mixed|Array or Boolean
     */
    private static function extractFromInt($number)
    {

        if (self::isValidNumber($number)) {
            return self::$names[$number];
        }

        return false;
    }

    /**
     * Converts input to and returns JSON
     * @param  Mixed $data
     * @return String JSON String
     */
    private static function getJSON($data)
    {
        return json_encode($data);
    }
}
