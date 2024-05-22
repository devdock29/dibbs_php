<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFactory;

use azharLogger\azharLoggerClass as azharLoggerClass;

class AzharFactory {

    private static $errorLogsFilePath = "";
    private static $errorLogsMod = 0; // 0 = will echo on page, 1 = write to error logs
    private static $objects = array();

    public static function init($dbCredentials) {
        if (isset($dbCredentials['errorLogsMod'])) {
            if (isset($dbCredentials['logFilePath'])) {
                self::$errorLogsFilePath = $dbCredentials['logFilePath'];
                self::$errorLogsMod = ($dbCredentials['errorLogsMod'] == 1 ? 1 : 0);
                azharLoggerClass::initLogger(array("errorLogsMod" => self::$errorLogsMod, "logFilePath" => self::$errorLogsFilePath));
            }
        }
    }

    public static function add($key, $object) {
        self::$objects[$key] = $object;
    }

    public static function getAllKeys() {
        return array_keys(self::$objects);
    }

    public static function get($key) {
        try {
            if (!self::isKeyExist($key)) {
                return false;
                //throw new \Exception('Key ' . $key . ' not registered.');
            }

            return self::$objects[$key];
        } catch (\Exception $ex) {
            azharLoggerClass::logIt(array("type" => "Factory", "contents" => "Exception: " . $ex->getMessage()));
        }
        return false;
    }

    public static function delete($key) {
        if ($this->isKeyExist($key)) {
            unset(self::$objects[$key]);
        }
    }

    public static function isKeyExist($key) {
        if (isset(self::$objects[$key])) {
            return true;
        } else {
            return false;
        }
    }

}
