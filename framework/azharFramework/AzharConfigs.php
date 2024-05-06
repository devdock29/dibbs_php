<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class AzharConfigs {

    private static $azharConfigs;
    private static $baseDir;
    private static $baseURL;

    public static function setBaseDir($dir) {
        self::$baseDir = $dir;
    }

    public static function getBaseDir() {
        return self::$baseDir;
    }

    public static function setBaseURL($url) {
        self::$baseURL = $url;
    }

    public static function getBaseURL() {
        return self::$baseURL;
    }

    public static function set($configs) {
        self::$azharConfigs = $configs;
    }

    public static function add($key, $val) {
        self::$azharConfigs[$key] = $val;
    }

    public static function all() {
        return self::$azharConfigs;
    }

    public static function get($key, $default = null) {
        if (!empty(self::$azharConfigs) && array_key_exists($key, self::$azharConfigs)) {
            return self::$azharConfigs[$key];
        } else {
            return $default;
        }
    }

    public static function getNthKey() {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        $aritterator = self::$azharConfigs;
        for ($i = 0; $i < $numargs; $i++) {
            if (isset($aritterator[$arg_list[$i]]) || array_key_exists($arg_list[$i], $aritterator)) {
                $aritterator = $aritterator[$arg_list[$i]];
            } else {
                return false;
            }
        }
        return $aritterator;
    }

}
