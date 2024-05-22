<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use azharFramework\routes\Info;

class URL {

    public static function url($encode = true) {
        if (!$encode) {
            return "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        }
        return htmlspecialchars("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", ENT_QUOTES, 'UTF-8');
    }

    public static function defaultLangURL() {
        if (Info::get('lang') !== null) {
            //return str_ireplace(Info::get('lang'), "", static::url(false));
            return static::missingLangURLs(Info::get('lang'));
        } else {
            return static::missingDefaultLangURLs();
        }
    }

    private static function missingLangURLs($_lang) {
        $lang = rtrim($_lang, "/");
        $altLangs = AzharConfigs::getNthKey("locale", "altLocale");
        $defaultLang = AzharConfigs::getNthKey("locale", "lang");
        $index = array_search($lang, $altLangs);
        if ($index !== false) {
            $altLangs[$index] = $defaultLang;
        }
        $urls = array();
        foreach ($altLangs as $lng) {
            $language = strtoupper($lng);
            if ($lng == $defaultLang) {
                $urls[] = array("lang" => $language, "url" => str_ireplace("/" . $lang, "", static::url(false)));
            } else {
                $urls[] = array("lang" => $language, "url" => str_ireplace($lang, $language, static::url(false)));
            }
        }
        return $urls;
    }

    private static function missingDefaultLangURLs() {
        $altLangs = AzharConfigs::getNthKey("locale", "altLocale");
        $urls = array();
        foreach ($altLangs as $lang) {
            $language = strtoupper($lang);
            $urls[] = array("lang" => $language, "url" => static::nonLocaleUrl() .
                $language .
                "/" .
                ltrim(static::requestURI(), "/"));
        }
        return $urls;
    }

    public static function requestURI() {
        return str_ireplace(array(AzharConfigs::getBaseURL(), (Info::get('module') !== null ? Info::get('module') . "/" : '')), "", $_SERVER['REQUEST_URI']);
    }

    public static function hostUrl($prefixHttp = "") {
        $url = "//" . static::host();
        if ($prefixHttp != "") {
            return static::getProtocol() . ":" . $url;
        }
        return $url;
    }

    public static function host() {
        return htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8');
    }

    public static function subDomain() {
        $arr = (explode(".", static::host()));
        return array_shift($arr);
    }

    public static function isSecure() {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    public static function getProtocol() {
        if (static::isSecure()) {
            return "https";
        } else {
            return "http";
        }
    }

    /* Return the non locale base url */

    public static function nonLocaleUrl($prefixHttp = "") {
        $url = rtrim(static::hostUrl($prefixHttp) . AzharConfigs::getBaseURL(), "/") . "/";
        if (Info::get('hasModule') !== null && Info::get('hasModule') == 'url') {
            $url .= Info::get('module') . "/";
        }
        return $url;
    }

    public static function baseUrl($prefixHttp = "") {
        //$url = static::hostUrl($prefixHttp) . AzharConfigs::getBaseURL();
        //$url = rtrim($url, "/") . "/";
        $url = static::nonLocaleUrl($prefixHttp);
        if (Info::get('lang') !== null) {
            $url .= strtoupper(Info::get('lang'));
        }
        return rtrim($url, "/") . "/";
    }

    public static function locale() {
        return Info::get('lang');
    }

}
