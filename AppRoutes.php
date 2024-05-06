<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

/* DO NOT REMOVE OR MISSPELL KEYS
 * Every thing is case sensitive
 * */
return array(
    "lang" => array("en/", "ar/"),
    "controllerNamespace" => array('rest/api/', 'services/', 'crons/'),
    "AppRoutesModel" => "AppRoutesModel",
    "modules" => array(
        "_admin" => array("directory" => "admin"), 
    ),
    "mixedPattern" => array(
    /* "jobs-in-*" => "Home@index@get", */
    ),
    "patterns" =>
    /* job-detail route will map detailAction of Job controller in which "Job"
     * is case sensitive controller name without controller postfix
     * and case sensitive "detail" is action name
     */
    array(
        /* "laninjadoor01-jobs" => 'Job@jsearch@get@{"q":"all","fsort":"created%20desc"}' */
        "how-to-install" => 'HowToInstall@index'
    ),
);
