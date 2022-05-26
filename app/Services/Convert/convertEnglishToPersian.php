<?php

namespace App\Services\Convert;

class convertEnglishToPersian{

    public static function convertEnglishToPersian($input)
    {
        $input = number_format($input);
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = [0,  1,  2,  3,  4,  5,  6,  7,  8,  9];
        return str_replace($english, $persian, $input);
    }
}
?>