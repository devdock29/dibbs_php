<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\routes;

class Info {

    public static $data;

    public static function set($key, $val) {
        self::$data[$key] = $val;
    }

    public static function getArr() {
        return self::$data;
    }

    public static function get($key) {
        return (isset(self::$data[$key]) && self::$data[$key] != '' ? self::$data[$key] : null);
    }

}
