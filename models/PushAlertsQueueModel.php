<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class PushAlertsQueueModel extends AppModel {

    protected $relation = 'push_alerts_queue';
    protected $pk = 'auto_id';
    
    public function addPush($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $insertArr = [
            "gcm_token" => $params['gcm_token'],
            "heading" => $params['heading'],
            "message" => $params['message'],
            "status" => 'pending',
        ];
        $insertArr['added_on'] = date("Y-m-d H:i:s");
        $insertArr['ip_address'] = \helpers\Common::getIP();

        $this->insert($insertArr);
        
        $retData["code"] = "11";
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("13", "lang_en")["lang_en"];
        return $retData;
    }
    
}
