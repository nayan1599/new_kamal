<?php 
function bn_number($number) {
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    $bangla  = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($english, $bangla, $number);
}
function takaInWordsBn($amount) {

    $words = [
        0 => 'শূন্য', 1 => 'এক', 2 => 'দুই', 3 => 'তিন', 4 => 'চার', 5 => 'পাঁচ',
        6 => 'ছয়', 7 => 'সাত', 8 => 'আট', 9 => 'নয়', 10 => 'দশ',
        11 => 'এগারো', 12 => 'বারো', 13 => 'তেরো', 14 => 'চৌদ্দ', 15 => 'পনের',
        16 => 'ষোল', 17 => 'সতেরো', 18 => 'আঠারো', 19 => 'উনিশ', 20 => 'বিশ',
        21 => 'একুশ', 22 => 'বাইশ', 23 => 'তেইশ', 24 => 'চব্বিশ', 25 => 'পঁচিশ',
        26 => 'ছাব্বিশ', 27 => 'সাতাশ', 28 => 'আটাশ', 29 => 'ঊনত্রিশ', 30 => 'ত্রিশ',
        31 => 'একত্রিশ', 32 => 'বত্রিশ', 33 => 'তেত্রিশ', 34 => 'চৌত্রিশ', 35 => 'পঁয়ত্রিশ',
        36 => 'ছত্রিশ', 37 => 'সাঁইত্রিশ', 38 => 'আটত্রিশ', 39 => 'ঊনচল্লিশ', 40 => 'চল্লিশ',
        41 => 'একচল্লিশ', 42 => 'বিয়াল্লিশ', 43 => 'তেতাল্লিশ', 44 => 'চুয়াল্লিশ',
        45 => 'পঁয়তাল্লিশ', 46 => 'ছেচল্লিশ', 47 => 'সাতচল্লিশ', 48 => 'আটচল্লিশ',
        49 => 'ঊনপঞ্চাশ', 50 => 'পঞ্চাশ', 60 => 'ষাট', 70 => 'সত্তর',
        80 => 'আশি', 90 => 'নব্বই'
    ];

    // 1-99
    $convertBelowHundred = function($n) use ($words) {
        if ($n <= 50) return $words[$n] ?? '';
        if ($n < 100) {
            $tens = floor($n / 10) * 10;
            $unit = $n % 10;
            return ($words[$tens] ?? '') . ($unit ? ' ' . $words[$unit] : '');
        }
        return '';
    };

    // 1-999
    $convertHundreds = function($n) use ($words, $convertBelowHundred) {
        $str = '';
        if ($n >= 100) {
            $str .= $words[floor($n/100)] . ' শত';
            $n %= 100;
            if ($n) $str .= ' ';
        }
        if ($n > 0) {
            $str .= $convertBelowHundred($n);
        }
        return $str;
    };

    if ($amount == 0) return 'শূন্য টাকা মাত্র';

    $number = floor($amount);
    $decimal = round(($amount - $number) * 100);

    $result = '';

    // কোটি
    if ($number >= 10000000) {
        $crore = floor($number / 10000000);
        $result .= $convertHundreds($crore) . ' কোটি ';
        $number %= 10000000;
    }

    // লক্ষ
    if ($number >= 100000) {
        $lakh = floor($number / 100000);
        $result .= $convertHundreds($lakh) . ' লক্ষ ';
        $number %= 100000;
    }

    // হাজার
    if ($number >= 1000) {
        $thousand = floor($number / 1000);
        $result .= $convertHundreds($thousand) . ' হাজার ';
        $number %= 1000;
    }

    // বাকি
    if ($number > 0) {
        $result .= $convertHundreds($number);
    }

    $result = trim($result) . ' টাকা';

    // পয়সা
    if ($decimal > 0) {
        $result .= ' ' . $convertBelowHundred($decimal) . ' পয়সা';
    }

    return $result . ' মাত্র';
}