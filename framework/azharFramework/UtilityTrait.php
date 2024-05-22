<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use azharFactory\AzharFactory as AzharFactory;
use azharFramework\solr\Solr;
use AzharConstants;

trait UtilityTrait {

    use traits\App;

    public function redirect($params) {
        if (!isset($params['url'])) {
            echo "URL is missing";
            return false;
        }
        $url = $params['url'];
        $statusCode = (isset($params['statusCode']) ? $params['statusCode'] : '302');
        if (isset($params['data']) && is_array($params['data'])) {
            $this->state()->setFlash($params['data']);
        } else {
            echo "Passing data should be an array";
        }
        header("Location: $url", true, $statusCode);
        exit;
    }

    public function getInfo($key) {
        return routes\Info::get($key);
    }

    public function getService($key) {
        return AzharFactory::get($key);
    }

    public function cache() {
        return $this->getService(\AzharConstants::CACHE);
    }

    public function state() {
        return $this->getService(\AzharConstants::SESSION);
    }

    public function getState($key) {
        return $this->state()->get($key);
    }

    public function setState($key, $value) {
        $this->state()->set($key, $value);
    }

    public function db($db = DEFAULT_DATABASE) {
        return $this->getService($db);
    }

    public function noSQL($db = DEFAULT_NO_SQL) {
        return $this->getService(AzharConstants::NOSQL . $db);
    }

    public function isGET() {
        if ($this->getService(\AzharConstants::REQUEST)->getRequestMethod() == \AzharConstants::GET) {
            return true;
        } else {
            return false;
        }
    }

    public function solr() {
        if (!$this->getService(\AzharConstants::SOLR)) {
            AzharFactory::add(\AzharConstants::SOLR, new Solr());
        }
        return $this->getService(\AzharConstants::SOLR);
    }

    public function validate() {
        if (!$this->getService(\AzharConstants::VALIDATE)) {
            AzharFactory::add(\AzharConstants::VALIDATE, new \azharFramework\data\Validate());
        }
        return $this->getService(\AzharConstants::VALIDATE);
    }

    public function isPOST() {
        if ($this->getService(\AzharConstants::REQUEST)->getRequestMethod() == \AzharConstants::POST) {
            return true;
        } else {
            return false;
        }
    }

    /* This method will return post vaue against specied key and if key not
     * specified then the whole post will return
     */

    public function post($key = null, $defaultValue = null) {
        if ($this->isPOST()) {
            if (isset($this->getService(\AzharConstants::POST)[$key])) {
                return $this->getService(\AzharConstants::POST)[$key];
            } else {
                return $defaultValue;
            }
        }
    }

    /* This method will return post vaue against specied key and if key not
     * specified then the whole post will return
     */

    public function get($key = null, $defaultValue = null) {
        if ($this->isGET()) {
            if (isset($this->getService(\AzharConstants::GET)[$key])) {
                return $this->getService(\AzharConstants::GET)[$key];
            } else {
                return $defaultValue;
            }
        }
    }

    public function obtainRawPostKey($key = null, $params = array(), $defaultValue = null) {
        if ($this->isPOST()) {
            if (isset($this->getService(\AzharConstants::POST_RAW)[$key])) {
                if (isset($params['allowedTags'])) {
                    return Security::NcheckValues($this->getService(\AzharConstants::POST_RAW)[$key], $params['allowedTags']);
                }
            }
        }
        return $defaultValue;
    }

    /* This function return the REQUEST array */

    public function obtainRequest() {
        return $this->getService(\AzharConstants::REQUEST);
    }

    /* This function return the headers array */

    public function obtainHeaders() {
        return $this->getService(\AzharConstants::HEADERS);
    }

    /* This function return the get array */

    public function obtainGet() {
        return $this->getService(\AzharConstants::GET);
    }

    /* This function return the post array */

    public function obtainPost() {
        return $this->getService(\AzharConstants::POST);
    }

    /* This function return the PUT array */

    public function obtainPut() {
        return $this->getService(\AzharConstants::PUT);
    }

    /* This function return the DELETE array */

    public function obtainDelete() {
        return $this->getService(\AzharConstants::DELETE);
    }

    public function isGuest() {
//var_dump($this->getState('_isGuestState_'));
        if ($this->getState('_isGuestState_') === null) {
            return true;
        } else {
            return $this->getState('_isGuestState_');
        }
    }

    public function getUserState($key = null) {
        if ($this->getState('user')) {
            $user = $this->getState('user');
            return ($key != null ? $user[$key] : $user);
        } else {
            return false;
        }
    }

    public function setGuest($bool) {
        $this->setState('_isGuestState_', $bool);
    }

    public function offset($perPage, $total, $offset = 0) {
        $nextOffset = $offset + $perPage;
        if ($offset + $perPage > $total) {
            return false;
        }
        return $nextOffset;
    }

    public function paginate($settings) {
        if (!$this->getService(\AzharConstants::PAGINATE)) {
            AzharFactory::add(\AzharConstants::PAGINATE, new \azharFramework\data\Paginate($settings));
        }
        return $this->getService(\AzharConstants::PAGINATE);
    }

}
