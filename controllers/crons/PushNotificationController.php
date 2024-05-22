<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\crons;

class PushNotificationController extends CronsController {

    public function indexAction() {
        $oPushAndroidAlerts = new \models\crons\SendPushModel();
        $oPushAndroidAlerts->run(0);
    }
    
    public function sendCampaignAction() {
        $oPushAndroidAlerts = new \models\crons\SendCampaignModel();
        $oPushAndroidAlerts->run(0);
    }
}

