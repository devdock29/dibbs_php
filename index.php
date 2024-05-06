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
?>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGQ7BSJHH0"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGQ7BSJHH0');
</script>
