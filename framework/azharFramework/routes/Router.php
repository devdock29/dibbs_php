<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\routes;

use azharFramework\helpers\AzharString as AzharString;
use azharFramework\AzharModel;
use \azharFactory\AzharFactory;
use \AzharConstants;
use \azharFramework\URL;

class Router {

    private static $postRoutes;
    private static $getRoutes;
    private static $appRoutes;
    private static $routeFilters = array("lang", "controllerNamespace");

    public static function loadRoutes($routes) {
        static::$appRoutes = $routes;
    }

    public static function getAppRoutes() {
        return self::$appRoutes;
    }

    public static function find($key) {
        /* Check whether AppRoutes file is available or not in application route */
        if (self::getAppRoutes() !== null) {
            return static::decideRoute($key);
        }
    }

    public static function decideRoute($key) {
        /* Trying to find for a File level route */
        $parttern = \azharFramework\helpers\Common::getNthKey(self::getAppRoutes(), "patterns", $key);
        $DBParttern = \azharFramework\helpers\Common::getNthKey(self::getAppRoutes(), "AppRoutesModel", $key);
        if ($parttern === false && $DBParttern===true) {
            /* if file level route not found then trying to db level route */
            return static::findDBRoute($key);
        } else {
            return $parttern;
        }
    }

    private static function mixedPatterns($key) {

        $partterns = \azharFramework\helpers\Common::getNthKey(self::getAppRoutes(), "mixedPattern");
        foreach ($partterns as $pattern => $route) {
            if (AzharString::lastChar($pattern) == "*") {//end with *
//                echo $finalPattern = rtrim($pattern, '*');
//                $pattern = "#^startText(.*)$#i";
//                if(preg_match($pattern, $finalPattern )==1){
//                    echo "<pre>";print_r($matches);exit;
//                }else echo "else";
            }exit;
        }
    }

    private static function findDBRoute($key) {
        $AppRoutesClass = \azharFramework\helpers\Common::getNthKey(self::getAppRoutes(), "AppRoutesModel");
        if ($AppRoutesClass) {
            $oAzharModel = new AzharModel();
            $oAppRouteModel = $oAzharModel->model($AppRoutesClass);
            if ($oAppRouteModel instanceof \azharFramework\interfaces\AppRoutes) {
                return $oAppRouteModel->route($key);
            }
        }
        return false;
    }

    public static function modules($uri) {
        if (isset(static::$appRoutes['modules'][URL::subDomain()])) {
            Info::set('hasModule', 'subdomain');
            Info::set('module', static::$appRoutes['modules'][URL::subDomain()]['directory']);
            Info::set('controllerNamespace', Info::get('module') . "/");
        }
        if (isset(static::$appRoutes['modules'])) {
            foreach (static::$appRoutes['modules'] as $_key => $value) {
                $key = $_key . "/";
                $pos = AzharString::startsWith($uri, $key);
                if ($pos !== false) {
                    Info::set('hasModule', 'url');
                    Info::set('module', static::$appRoutes['modules'][$_key]['directory']);
                    Info::set('controllerNamespace', Info::get('module') . "/");
                    return substr($uri, strlen($key));
                }
            }
        }
        return $uri;
    }

    public static function run($uri, $baseURL) {
        $parsedURI = explode("?", $uri);
        $uri = $parsedURI[0];
        $uri = trim($uri, "/") . "/";
        /* first try to find subdomain route if any */
        $uri = static::modules($uri);
        foreach (static::$routeFilters as $key => $value) {
            $out = static::applyFilter($uri, $value);
            if ($out || $out == -10) {
                if ($out == -10) {
                    $out = "";
                }
                $uri = $out;
            }
        }
        /* trying to reset the locale if found in URI */
        if (\azharFramework\routes\Info::get('lang') !== null) {
            \azharFramework\lang\Locale::set(trim(\azharFramework\routes\Info::get('lang'), "/"));
        }
        return static::build($uri, $baseURL);
    }

    private static function applyFilter($uri, $infoKey) {
        if (isset(static::$appRoutes[$infoKey])) {
            $filtersDataArr = static::$appRoutes[$infoKey];
            foreach ($filtersDataArr as $key => $value) {
                $pos = AzharString::startsWith($uri, $value);
                if ($pos !== false) {
                    if (static::$infoKey($value)) {
                        $newURI = substr($uri, strlen($value));
                        if (Info::get($infoKey) !== null) {
                            Info::set($infoKey, Info::get($infoKey) . $value);
                        } else {
                            Info::set($infoKey, $value);
                        }
                        return ($newURI == '' ? '-10' : $newURI);
                    } else {
                        return $uri;
                    }
                }
            }
        }
        return false;
    }

    /* lang and controllerNamespace are method being called in applyFilter function
     *  these methods are directly propotional to $routeFilters, mean if some body is
     *  going to add some entry in $routeFilters then it has to add a new function
     * nameded in $routeFilters
     */

    public static function lang($_key) {
        $key = strtolower(trim($_key, "/"));
        $altAppLangs = \azharFramework\AzharConfigs::getNthKey("locale", "altLocale");
        //if (strtolower(\azharFramework\AzharConfigs::getNthKey("locale", "lang")) != $key && strtolower(\azharFramework\AzharConfigs::getNthKey("locale", "altLocale")) != $key) {
        if (strtolower(\azharFramework\AzharConfigs::getNthKey("locale", "lang")) != $key &&
                !in_array($key, \azharFramework\AzharConfigs::getNthKey("locale", "altLocale"))) {
            return false;
        }
        return true;
    }

    public static function controllerNamespace() {
        /* do something in case of controller namespace found which is not required
         * right now
         */
        return true;
    }

    public static function setRouteData($_data) {
        if (isset($_data[3])) {
            AzharFactory::add((isset($_data[2]) ? strtoupper($_data[2]) : AzharConstants::GET), json_decode($_data[3], true));
        }
    }

    private static function build($uri, $baseURL) {
        $urlParts = array("home", "index");
        if ($uri != '') {
            $urlParts = explode('/', $uri);
            /* Tries to find the Routes in patterns if any */
            $rotueData = static::find(rtrim($uri, "/"));
            if (!$rotueData) {
                $rotueData = static::find($urlParts[0]);
            }
            // if ($rotueData) {
            //     $_data = explode("@", $rotueData);
            //     static::setRouteData($_data);
            //     //setting action name at 0th place
            //     $urlParts[0] = $_data[1];
            //     //now prepend controller name at start of array
            //     array_unshift($urlParts, $_data[0]);
            //     Info::set("controller", $_data[0]);
            //     Info::set("action", $_data[1]);
            //     Info::set("requestMethod", $_data[2]);
            // } else {
            //     Info::set("controller", ucfirst((isset($urlParts[0]) ? $urlParts[0] : null)));
            //     Info::set("action", (isset($urlParts[1]) ? $urlParts[1] : null));
            // }
        } else {
            Info::set("controller", "Home");
            Info::set("action", "index");
        }
        return $urlParts;
    }

    public static function run2($uri, $baseURL) {
        foreach (static::$routeFilters as $key => $value) {
            $out = static::applyFilter(trim($uri, "/"), trim($value, "/"));
            if ($out || $out == -10) {
                if ($out == -10) {
                    $out = "";
                }
                $uri = $out;
            }
        }
        return static::build($uri, $baseURL);
    }

    private static function applyFilter2($uri, $infoKey) {
        if (isset(static::$appRoutes[$infoKey])) {
            $filtersDataArr = static::$appRoutes[$infoKey];
            foreach ($filtersDataArr as $key => $value) {
                //$result = preg_match("#^$value(.*)$#i", $uri);
                //$result = preg_replace('/' . preg_quote('[{'.$value.']') . '.*?' . '/', "", $uri);
//                $result = preg_replace("#^$value(.*)$#i","", $uri);
//                echo "$infoKey:::".$result."<br>";
//                if ($result == 0) {
//                    echo "No match";
//                } else {
//                    echo "<br>Match found for." . $uri . "," . $value;
//                    exit;
//                }
                $pos = AzharString::startsWith($uri, trim($value, "/"));
                if ($pos !== false) {
                    $newURI = substr($uri, strlen($value));
                    Info::set($infoKey, $value);
                    return ($newURI == '' ? '-10' : $newURI);
                }
            }
        }
        return false;
    }

    public static function getControllerNamespaces() {
        if (isset(static::$appRoutes['controllerNamespace'])) {
            return static::$appRoutes['controllerNamespace'];
        }
        return false;
    }

    public static function parseNamespace($uri) {
        if (static::getControllerNamespaces()) {
            foreach (static::getControllerNamespaces() as $key => $value) {
                $pos = stripos($uri, $value);
                if ($pos !== false) {
                    $newURI = substr($uri, strlen($value));
                    if (trim($newURI) != '') {
                        return array("uri" => $newURI, "controllerNamespace" => $value);
                    }
                }
            }
        }
        return false;
    }

    public static function post($key, $value) {
        self::$postRoutes[$key] = $value;
    }

    public static function get($key, $value) {
        self::$getRoutes[$key] = $value;
    }

}