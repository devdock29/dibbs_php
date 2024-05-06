<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

if (isset($argc) && isset($argv)) {
    include_once("conf/site-config.php");
    include_once("conf/frameworkLoader.php");
    $oNasFramework = new azharFramework\AzharFramework(
        array(
            "DIR" => __DIR__,
            "nasConfigs" => $nasConfigs,
            "baseURL" => ''
        )
    );
    $oNasFramework->console(array('argv' => $argv, 'argc' => $argc));
}