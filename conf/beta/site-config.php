<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+
/*ini_set('display_error', 1);
ini_set('display_startup_error', 1);
error_reporting(E_RROR);*/

define("SITE_AT", "beta");
$baseURL = '';
define("SITE_URL", "https://dibbs.sastidukan.com/");
define("ASSETS_URL", "https://dibbs.sastidukan.com/assets/");
define("ADMIN_URL", "https://dibbs.sastidukan.com/_admin/");

$azConfigs = array(
    "app" => array("name" => "Dibbs", "namespace" => ''),
    "locale" => array("lang" => "en", "altLocale" => "en", "langPath" => "lang", "skipLang" => "en", "langFile" => "messages"),
    "errorLogs" => array("debug" => 0, "logsFilePath" => "errorLogs/"),
    "cache" => array("enable" => true, "rzCache" => true, "path" => ""),
    "db" => array(
        "DATABASE" => array(
            "host" => 'localhost',
            "dbName" => 'sastiduk_dibbs',
            "userName" => "sastiduk_dibbs",
            "password" => "dbAngle@20",
            "isDefault" => 1
        ),
        "OTHER" => array(
            "host" => 'localhost',
            "dbName" => 'OTH',
            "userName" => "OTH",
            "password" => 'OTH',
            "isDefault" => 0
        ),
    )
);

define("IMAGES_PATH", "/uploads/");
// Brand Variables
define("DOMAIN_NAME", "Dibbs");
define("DOMAIN_BRAND_NAME", "Dibbs");
define("FROM_EMAIL", "no-reply@dibbs.com");
define("BCC_EMAIL", "azharwaris@gmail.com");
