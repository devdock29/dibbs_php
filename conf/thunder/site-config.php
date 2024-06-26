<?php


define("SITE_AT", "thunder");
$baseURL = '';
define("SITE_URL", "https://dibbs.thundertechsol.com/");
define("ASSETS_URL", "https://dibbs.thundertechsol.com/assets/");
define("ADMIN_URL", "https://dibbs.thundertechsol.com/_admin/");

$azConfigs = array(
    "app" => array("name" => "Dibbs", "namespace" => ''),
    "locale" => array("lang" => "en", "altLocale" => "en", "langPath" => "lang", "skipLang" => "en", "langFile" => "messages"),
    "errorLogs" => array("debug" => 0, "logsFilePath" => "errorLogs/"),
    "cache" => array("enable" => true, "rzCache" => true, "path" => ""),
    "db" => array(
        "DATABASE" => array(
            "host" => 'localhost',
            "dbName" => 'thundertechsol_dibbs',
            "userName" => "thundertechsol_dibbsuser",
            "password" => "DibbsUser1$",
            "isDefault" => 1
        )
    )
);

define("IMAGES_PATH", "/uploads/");
// Brand Variables
define("DOMAIN_NAME", "Dibbs");
define("DOMAIN_BRAND_NAME", "Dibbs");
define("FROM_EMAIL", "no-reply@thundertechsol.com");
define("BCC_EMAIL", "fibrahimthunder@gmail.com");