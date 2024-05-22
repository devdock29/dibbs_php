<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

date_default_timezone_set('UTC');
include_once("conf/site-config.php");
include_once("conf/frameworkLoader.php");
$oAzFramework = new azharFramework\AzharFramework(
    array(
        "DIR" => __DIR__,
        "azharConfigs" => $azConfigs,
        "baseURL" => $baseURL
    )
);
if(!is_dir("errorLogs")) {
    mkdir("errorLogs", 0777, true);
}
$oAzFramework->addRoutes(require 'AppRoutes.php');
$oAzFramework->run();

