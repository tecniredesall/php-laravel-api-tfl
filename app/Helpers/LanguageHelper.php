<?php


namespace App\Helpers;


class LanguageHelper
{
    const ES = "es";

    const EN = "en";

    const MX = "mx";

    const US = "us";

    public function Normalize(string $lang)
    {
        $lang = strtolower(trim($lang));
        if ($lang == self::ES || $lang == self::MX) {
            return self::ES;
        }

        if($lang == self::EN || $lang == self::US){
            return self::EN;
        }

        return self::EN;
    }
}