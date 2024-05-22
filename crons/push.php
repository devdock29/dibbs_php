<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include('../conf/site-config.php');

$file = file_get_contents(SITE_URL . "crons/PushNotification/sendCampaign");
print_r($file);

$file = file_get_contents(SITE_URL . "crons/PushNotification/index");
print_r($file);