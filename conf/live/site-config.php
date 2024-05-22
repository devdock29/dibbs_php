<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

define("SITE_AT", "live");
$baseURL = '/';
define("SITE_URL", "http://www.coursefinder.pk/");

$azConfigs = array(
    "app" => array("name" => "coursefinder", "namespace" => ''),
    "locale" => array("lang" => "en", "altLocale" => "en", "langPath" => "lang", "skipLang" => "en", "langFile" => "messages"),
    "errorLogs" => array("debug" => 0, "logsFilePath" => "errorLogs/"),
    "cache" => array("enable" => true, "rzCache" => true, "path" => ""),
    "db" => array(
        "DATABASE" => array(
            "host" => 'localhost',
            "dbName" => 'ninja',
            "userName" => "ninja",
            "password" => "N!nJ@D00r",
            "isDefault" => 1
        ),
        "EASYTICKETS" => array(
            "host" => 'localhost',
            "dbName" => 'easytickets',
            "userName" => "imeticket",
            "password" => 'E@syTicLnM',
            "isDefault" => 0
        ),
    )
);

define("IMAGES_PATH", "/images/");
