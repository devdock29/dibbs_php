<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

$server_env = getServEnv();
require_once("$server_env/site-config.php");

$azConfigs['encoding']['salt'] = 'Y!O*U%R&D@O!M%A$I!N';

// ----------------
function getServEnv() {
    $serverName = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    if(!empty($serverName)) {
        if(stristr($_SERVER['SERVER_NAME'], "localhost") || stristr($_SERVER['REQUEST_URI'], "localhost")) {
            return "local";
        } elseif(stristr($_SERVER['SERVER_NAME'], "sastidukan") || stristr($_SERVER['REQUEST_URI'], "sastidukan")) {
            return "beta";
        } elseif(stristr($_SERVER['SERVER_NAME'], "thedibbsapp") || stristr($_SERVER['REQUEST_URI'], "thedibbsapp")) {
            return "demo";
        } elseif(stristr($_SERVER['SERVER_NAME'], "thundertechsol") || stristr($_SERVER['REQUEST_URI'], "thundertechsol")) {
            return "thunder";
        } else {
            return "live";
        }
    } else {
        $pwd = $_SERVER['PWD'];

        if(stristr($pwd, "/htdocs")) {
            return "local";
        } elseif(stristr($pwd, "dibbs")) {
            return "beta";
        } elseif(stristr($pwd, "htdocs")) {
            return "demo";
        } elseif(stristr($pwd, "dibbst")) {
            return "thunder";
        } else {
            return "live";
        }
    }
}