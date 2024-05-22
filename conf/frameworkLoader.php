<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

spl_autoload_register(function ($classname) {
    $cFile = getcwd() . '/framework/' . str_ireplace("\\", "/", $classname . ".php");
    if(is_file($cFile)) {
        include $cFile;
    }
});
