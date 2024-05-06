<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+
ini_set('display_error', 1);
ini_set('display_startup_error', 1);
error_reporting(E_ERROR);

define("SITE_AT", "local");
$baseURL = '/dibbs/';
define("SITE_URL", "http://localhost/dibbs/");
define("ASSETS_URL", "http://localhost/dibbs/assets/");
define("ADMIN_URL", "http://localhost/dibbs/_admin/");

$azConfigs = array(
    "app" => array("name" => "dibbs", "namespace" => ''),
    "locale" => array("lang" => "en", "altLocale" => "en", "langPath" => "lang", "skipLang" => "en", "langFile" => "messages"),
    "errorLogs" => array("debug" => 1, "logsFilePath" => "errorLogs/"),
    "cache" => array("enable" => false, "rzCache" => false, "path" => ""),
    "db" => array(
        "DATABASE" => array(
            "host" => 'localhost',
            "dbName" => 'dibbs',
            "userName" => "root",
            "password" => "",
            "isDefault" => 1
        )/*,
        "SECONDB" => array(
            "host" => 'localhost',
            "dbName" => 'root',
            "userName" => "",
            "password" => '',
            "isDefault" => 0
        ),*/
    )
);

define("IMAGES_PATH", "/uploads/");
// Brand Variables
define("DOMAIN_NAME", "Dibbs");
define("DOMAIN_BRAND_NAME", "Dibbs");
define("FROM_EMAIL", "no-reply@dibbs.com");
define("BCC_EMAIL", "azharwaris@gmail.com");
