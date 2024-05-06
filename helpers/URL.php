<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace helpers;

class URL extends \azharFramework\URL {

    public static function get($key = null, $data = null) {
        switch ($key) {
            case 'compLogos':
                return ASSETS_URL . 'company_logos/';
            case 'assets':
                return ASSETS_URL;
            case 'services':
                return static::baseURL() . 'services/';
            case 'defaultLangURL':
                return static::defaultLangURL();
            case 'base_url':
                return static::baseURL();
            case 'seeker':
                return SEEKER_URL . strtoupper(static::locale());
            case 'hiring':
                return HIRING_URL . strtoupper(static::locale());
            default :
                return SITE_URL;
        }
    }

    public static function nonSecure($prefixHttp = "") {
        $bUrl = str_replace("secure", "sandbox", static::baseURL($prefixHttp));
        if (strtolower(SITE_AT) == 'live') {
            $bUrl = str_replace("secure", "www", static::baseURL($prefixHttp));
        }
        return str_replace("https", "http", $bUrl);
    }

    public static function addhttp($url) {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $http = "https:";
        } else {
            $http = "http:";
        }
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = $http . $url;
        }
        return $url;
    }

    public static function getOfficialUrl($arr) {
        $info = $arr['engineInfo'];
        if ($info && $info['officialURL'] != '') {
            $url = trim($info['officialURL'], '/') . '/';
            if (strtolower(SITE_AT) == 'beta')
                $url = $url . "mcp01-beta/";
        }
        else {
            if (strtolower(SITE_AT) == 'Beta') {
                $url = ($arr['webId'] == '1' ? 'https://beta.rozee.pk/' : 'https://beta.mihnati.com/');
            } else {
                $url = ($arr['webId'] == '1' ? 'https://www.rozee.pk/' : 'https://www.mihnati.com/');
            }
        }
        return $url;
    }

    public static function canonical($URL) {
        // Remove default Locale from URL
        // Remove ?chlng from URL
        $lang = \nasFramework\NasConfigs::getNthKey('locale', 'lang');
        $URL = str_ireplace("/$lang", '', $URL);
        $URL = explode('?chlng', $URL)[0];
        return $URL;
    }

}