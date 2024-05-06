<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class Cookie {

    // Set the cookie on domain path
    function set($params) {
        //$cName, $cValue, $cExpiry, $cPath="/"
        header('P3P: policyref="' . $params['siteUrl'] . '"/w3c/p3p.xml", CP="ALL DSP COR CUR ADMa DEVa PSAa PSDa TAIa OUR BUS COM DEM INT NAV OTC PRE PUR STA UNI"');
        $isSecure = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? true : false);
        return setcookie($params['cName'], $params['cValue'], $params['cExpire'], $params['cPath'], $params['domainName'], $isSecure, true);
    }

    public function get($name) {
        return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : null);
    }

    public function delete($params) {
        unset($_COOKIE[$params['cName']]);
        $this->set($params);
    }

}
