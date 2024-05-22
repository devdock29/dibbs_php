<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class UsersGcmModel extends AppModel {

    protected $relation = 'users_gcm';
    protected $pk = 'auto_id';
    
    public function addGcm($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['gcm_id'])) {
            $retData["message"] = $oLangsModel->findByPK("30", "lang_en")["lang_en"];
            return $retData;
        }
        $this->delete(["whereClause" => " customer_id = ? ", "whereParams" => ["i", $params['customer_id']]]);
        $insertArr = [
            "customer_id" => $params['customer_id'],
            "gcm_id" => $params['gcm_id'],
            "platform" => "android",
        ];
        $insertArr['added_on'] = date("Y-m-d H:i:s");
        //$insertArr['added_by'] = $params['customer_id'];
        //$insertArr['ip_address'] = \helpers\Common::getIP();

        $this->insert($insertArr);
        
        $retData["code"] = "11";
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("13", "lang_en")["lang_en"];
        return $retData;
    }
    
    public function userGcm($user_id) {
        $userGcm = $this->find([
            "fields" => "gcm_id",
            "whereClause" => " customer_id = ? ",
            "whereParams" => ["i", $user_id],
        ]);
        return $userGcm['gcm_id'];
    }
}
