<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

define("SITE_AT", "demo");
$baseURL = '';
define("SITE_URL", "https://store.thedibbsapp.com/");
define("ASSETS_URL", "https://store.thedibbsapp.com/assets/");
define("ADMIN_URL", "https://store.thedibbsapp.com/_admin/");

$azConfigs = array(
    "app" => array("name" => "Dibbs", "namespace" => ''),
    "locale" => array("lang" => "en", "altLocale" => "en", "langPath" => "lang", "skipLang" => "en", "langFile" => "messages"),
    "errorLogs" => array("debug" => 0, "logsFilePath" => "errorLogs/"),
    "cache" => array("enable" => true, "rzCache" => true, "path" => ""),
    "db" => array(
        "DATABASE" => array(
            "host" => 'localhost',
            "dbName" => 'thediapp_new_dibbs',
            "userName" => "thediapp_new",
            "password" => "dbAngle@20",
            "isDefault" => 1
        )
    )
);

define("IMAGES_PATH", "/uploads/");
// Brand Variables
define("DOMAIN_NAME", "Dibbs");
define("DOMAIN_BRAND_NAME", "Dibbs");
define("FROM_EMAIL", "no-reply@thedibbsapp.com");
define("BCC_EMAIL", "azharwaris@gmail.com");