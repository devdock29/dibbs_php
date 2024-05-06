<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\lang;

use \azharFactory\AzharFactory;
use \azharFramework\AzharConfigs;
use azharFramework\routes\Info;

class Lang {

    private static $messages;
    private static $moduleMessages;
    private static $langPath;
    private static $skipLang;
    private static $altLang;
    private static $altLangPath;
    private static $altMessages;
    private static $module = "";

    private static function module() {
        if (Info::get('module') !== null) {
            self::$module = Info::get('module') . DIRECTORY_SEPARATOR;
        }
        return self::$module;
    }

    private static function filesPath() {
        return (\azharFramework\AzharConfigs::getNthKey("locale", "altLangPath") ? \azharFramework\AzharConfigs::getNthKey("locale", "altLangPath") : \azharFramework\AzharConfigs::getBaseDir() . DIRECTORY_SEPARATOR . \azharFramework\AzharConfigs::getNthKey("locale", "langPath") . DIRECTORY_SEPARATOR );
    }

    private static function _init($module) {
        self::$skipLang = \azharFramework\AzharConfigs::getNthKey("locale", "skipLang");
        self::$langPath = static::filesPath() .
                ($module ? self::module() : '') .
                Locale::get() . DIRECTORY_SEPARATOR .
                \azharFramework\AzharConfigs::getNthKey("locale", "langFile") . ".php";
        if ($module) {
            if (is_file(self::$langPath)) {
                self::$moduleMessages = require_once self::$langPath;
            }
        } else {
            self::$messages = require_once self::$langPath;
        }
    }

    public static function rtl() {
        $languages = array("ar", "ur");
        if (in_array(Locale::get(), $languages)) {
            return "rtl";
        } else {
            return "";
        }
    }

    private static function initAltLocale($module) {
        self::$altLang = \azharFramework\AzharConfigs::getNthKey("locale", "altLocale");
        foreach(self::$altLang as $lang){
            self::$altLangPath = \azharFramework\AzharConfigs::getBaseDir() .
                    DIRECTORY_SEPARATOR .
                    \azharFramework\AzharConfigs::getNthKey("locale", "langPath") .
                    DIRECTORY_SEPARATOR .
                    ($module ? self::module() : '') .
                    $lang . DIRECTORY_SEPARATOR .
                    \azharFramework\AzharConfigs::getNthKey("locale", "langFile") . ".php";
            if (is_file(self::$altLangPath)) {
                self::$altMessages = require_once strtolower(self::$altLangPath);
            }
        }
    }

    public static function get($key, $page = null, $_module = true, $prefix = false) {
        $module = false;
        if ($_module && Info::get('hasModule')) {
            $module = true;
        }

        if ($module) {
            return static::getModuleMsg($key, $page, $_module, $prefix);
        }
        if (self::$messages === null) {
            self::_init($module);
        }
        $_page = ($page === null ? AzharFactory::get("__controller__")->getLangIndex() : $page);
        if ($prefix) {
            $_page = \azharFramework\AzharConfigs::getNthKey("app", "namespace") . $_page; //(\azharFramework\AzharConfigs::getNthKey("app", "namespace")?\azharFramework\AzharConfigs::getNthKey("app", "namespace").".":'').$_page;
        }
        return (isset(self::$messages[$_page][$key]) ? self::$messages[$_page][$key] : self::getAlt($key, $_page, $module));
    }

    public static function getModuleMsg($key, $page = null, $module = true, $prefix = false) {
        if (self::$moduleMessages === null) {
            self::_init($module);
        }
        $_page = ($page === null ? AzharFactory::get("__controller__")->getLangIndex() : $page);
        if ($prefix) {
            $_page = \azharFramework\AzharConfigs::getNthKey("app", "namespace") . $_page;
        }
        return (isset(self::$moduleMessages[$_page][$key]) ? self::$moduleMessages[$_page][$key] : self::getAlt($key, $_page, $module));
    }

    public static function getAlt($key, $page, $module) {
        if (self::$altMessages === null) {
            self::initAltLocale($module);
        }
        return (isset(self::$altMessages[$page][$key]) ? self::$altMessages[$page][$key] : $key);
    }

    public static function preLocaleStr($str, $separator = "_") {
        if (Locale::get() == self::$skipLang) {
            return $str;
        }
        return Locale::get() . $separator . $str;
    }

    public static function getSkipLang() {
        if (self::$skipLang === null) {
            self::$skipLang = (AzharConfigs::getNthKey("locale", "skipLang"));
        }
        return self::$skipLang;
    }

    public static function postLocaleStr($str, $separator = "_") {
        if (Locale::get() == static::getSkipLang()) {
            return $str;
        }
        return $str . $separator . Locale::get();
    }

    public static function langList() {
        
    }

}
