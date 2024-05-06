<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\twig;

use azharFactory\AzharFactory;
use \azharFramework\lang\Lang;
use \azharFramework\lang\Locale;

class Nas {

    public static function __callStatic($name, $arguments) {
        $methodArgs = $arguments;
        $argsCount = count($arguments);
        if ($argsCount > 0) {
            if (method_exists('azharFramework\twig\Nas', $arguments[0])) {
                $methodName = $arguments[0];
                if (isset($arguments[1])) {
                    return self::$methodName($arguments[1]);
                } else {
                    return self::$methodName();
                }
            } else {
                return self::loadMehtod($argsCount, $methodArgs, $arguments);
            }
        }
    }

    private static function loadMehtod($argsCount, $methodArgs, $arguments) {
        if ($argsCount > 2) {
            array_shift($methodArgs); //shifting class name
            array_shift($methodArgs); //shifting method name
        }
        $oReflection = new \ReflectionMethod($arguments[0], $arguments[1]);
        if ($oReflection->isStatic()) {
            return $oReflection->invokeArgs(null, $methodArgs);
        } else {
            return $oReflection->invokeArgs(new $arguments[0], $methodArgs);
        }
    }

    public static function rtl() {
        return Lang::rtl();
    }

    public static function locale() {
        return Locale::get();
    }

    public static function state($key) {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->state()->get($key);
        }
    }

    public static function getUserState($key = null) {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->getUserState($key);
        }
    }

    public static function getFlash($key = null) {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->state()->getFlash($key);
        }
    }

    public static function isGuest() {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->isGuest();
        }
    }

    public static function dump() {
        
    }

    public static function baseURL($prefixHttp = "") {
        return \azharFramework\URL::baseUrl($prefixHttp);
    }

    public static function url() {
        return \azharFramework\URL::url();
    }

    public static function defaultLangURL() {
        return \azharFramework\URL::defaultLangURL();
    }

    public static function constant($name) {
        return constant($name);
    }

    public static function loadMeta() {
        $oMeta = AzharFactory::get(\AzharConstants::META);
        if ($oMeta) {
            return $oMeta->getHead();
        }
    }

    public static function loadJsGlobal() {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->loadJsGlobal();
        }
    }

    public static function loadCss() {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->loadCss();
        }
    }

    public static function loadScripts() {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->loadScripts();
        }
    }

    public static function module($postfix = "") {
        $oController = AzharFactory::get('__controller__');
        if ($oController) {
            return $oController->module($postfix);
        }
    }

    public static function encodeIntShortcut($str) {
        return \azharFramework\AzharHelper::encodeIntShortcut($str);
    }

    public static function decodeIntShortcut($str) {
        return \azharFramework\AzharHelper::decodeIntShortcut($str);
    }

}
