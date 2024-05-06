<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class Session {

    private $flasKey = "_fKey_";

    public function getIsActive() {
        if (version_compare(phpversion(), "5.4.0", ">=")) {
            return session_status() == PHP_SESSION_ACTIVE;
        } else {
            return (session_id() === '' ? false : true);
        }
    }

    public function set($key, $value) {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        $this->start();
        return (isset($_SESSION[$key]) ? $_SESSION[$key] : $default);
    }

    public function setFlash($data) {
        if (is_array($data)) {
            $flashes = $this->getAllFlashes([]);
            $this->set($this->getFlashKey(), array_merge($data, $flashes));
        } else {
            echo "Data should be an array";
            exit;
        }
    }

    public function all($default = null) {
        $this->start();
        return $_SESSION;
    }

    public function getAllFlashes($default = null, $remove = true) {
        $flashes = $this->get($this->getFlashKey(), $default);
        if ($remove) {
            $this->remove($this->getFlashKey());
        }
        return $flashes;
    }

    public function getFlash($key) {
        $value = "";
        $flashes = $this->getAllFlashes([]);
        if (isset($flashes[$key])) {
            $value = $flashes[$key];
            unset($flashes[$key]);
            $this->set($this->getFlashKey(), $flashes);
        }
        return $value;
    }

    public function getFlashKey() {
        return $this->flasKey;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function start() {
        if ($this->getIsActive()) {
            return;
        }
        if (!session_start()) {
            //handle error in case of session failure
        }
    }

    public function id() {
        return session_id();
    }

    public function removeAll() {
        $this->start();
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        if ($this->getIsActive()) {
            session_destroy();
            $_SESSION = '';
            session_unset();
        }
    }

}
