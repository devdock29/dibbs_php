<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\traits;

use \azharFramework\lang\Locale;
use \azharFramework\lang\Lang;
use \azharFramework\lang\Rtf;
//use \azharFramework\NasUploader;
use \azharFactory\AzharFactory;
use azharFramework\URL;
use azharFramework\routes\Info;
use \azharFramework\AzharConfigs;
use azharFramework\converter\PDF;

trait App {

    public function monitor($return = false) {
        if ($return) {
            return \azharFramework\SQLMonitor::get();
        } else {
            \azharFramework\SQLMonitor::show();
        }
    }

    public function pdf() {
        if (!$this->getService(\AzharConstants::PDF)) {
            AzharFactory::add(\AzharConstants::PDF, new PDF());
        }
        return $this->getService(\AzharConstants::PDF);
    }

    protected function jsonResponse($status = false, $message = 'Invalid Response Error', $code = '444') {
        //ob_clean();// remove warning messages if available
        header('Content-Type:application/json');
        die(json_encode(['status' => $status, 'message' => $message, 'code' => $code], JSON_PRETTY_PRINT));
    }

    public static function buildResponse($is_success, $response = array(), $errCode = null) {
        header('Content-Type:application/json');
        if($errCode == '00') {
            header("HTTP/1.1 422");
        }
        $data['success'] = $is_success;
        $data['response'] = $response;
        $data['code'] = (!is_null($errCode) ? strval($errCode) : '11');
        return json_encode($data);
    }

    public function module($postfix = "") {
        return Info::get('module') . $postfix;
    }

    public function addToConfig($key, $val) {
        AzharConfigs::add($key, $val);
    }

    public function getAzharConfigsByKey($key) {
        return AzharConfigs::get($key);
    }

    public function response() {
        if (!$this->getService(\AzharConstants::RESPONSE)) {
            AzharFactory::add(\AzharConstants::RESPONSE, new \azharFramework\Response());
        }
        return $this->getService(\AzharConstants::RESPONSE);
    }

    public function url() {
        return URL::url();
    }

    public function request() {
        if ($this->getService(\AzharConstants::REQUEST)) {
            return $this->getService(\AzharConstants::REQUEST);
        }
    }

    public function meta($oMeta = null) {
        if (!$this->getService(\AzharConstants::META)) {
            if ($oMeta === null) {
                return $oMeta;
            }
            AzharFactory::add(\AzharConstants::META, new \azharFramework\Meta($oMeta));
        }
        return $this->getService(\AzharConstants::META);
    }

    public function cookie() {
        if (!$this->getService(\AzharConstants::COOKIE)) {
            AzharFactory::add(\AzharConstants::COOKIE, new \azharFramework\Cookie());
        }
        return $this->getService(\AzharConstants::COOKIE);
    }

    public function baseUrl($prefixHttp = "") {
        return URL::baseUrl($prefixHttp);
    }

    /*public function uploader() {
        if (!$this->getService(\AzharConstants::UPLOADER)) {
            AzharFactory::add(\AzharConstants::UPLOADER, new NasUploader());
        }
        return $this->getService(\AzharConstants::UPLOADER);
    }*/

    public static function setLocale($locale) {
        Locale::set($locale);
    }

    public static function getLocale() {
        return Locale::get();
    }

    public static function localeMsg($key, $page = null, $module = true, $prefix = false) {
        return Lang::get($key, $page, $module, $prefix);
    }

    public static function rtf($key, $page = null, $module = true, $prefix = false) {
        return Rtf::get(Lang::get($key, $page, $module, $prefix));
    }

    public static function preLocaleStr($str, $separator = "_") {
        return Lang::preLocaleStr($str, $separator);
    }

    public static function postLocaleStr($str, $separator = "_") {
        return Lang::postLocaleStr($str, $separator);
    }

    public function __call($name, $arguments) {
        if ($name === 'getStackTrace') {
            return $this->getStackTrace2();
        }
    }

    public static function __callStatic($name, $arguments) {
        if ($name === 'getStackTrace') {
            if (count($arguments) > 0) {
                return self::getStackTrace1($arguments[0]);
            }
        }
    }

    public function userAgent() {
        return strtolower($this->getService(\AzharConstants::SECURITY)->NcheckValues($_SERVER['HTTP_USER_AGENT']));
    }

}
