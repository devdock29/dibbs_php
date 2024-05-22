<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\lang;

use \azharFramework\AzharConfigs;
use azharFramework\routes\Info;

class Locale {

    private static $locale;

    public static function set($locale) {
        self::$locale = $locale;
    }

    public static function get() {
        if (self::$locale === null) {
            if (Info::get('lang') !== null) {
               echo self::$locale = set(trim(\azharFramework\routes\Info::get('lang'), "/"));
            } else {
                $lang = AzharConfigs::getNthKey("locale", "lang");
                self::$locale = ($lang ? $lang : AzharConfigs::getNthKey("locale", "altLocale"));
            }
        }
        return self::$locale;
    }

}
