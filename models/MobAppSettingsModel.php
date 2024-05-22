<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class MobAppSettingsModel extends AppModel {

    protected $relation = 'mob_app_settings';
    protected $pk = 'app_id';

    public function getApiAuthSettings($appId, $appKey) {
        if (!empty($appId) && !empty($appKey)) {
            $appInfo = $this->find(["fields" => "*", "whereClause" => " app_id = ? AND app_key = ? AND app_status = ? ", "whereParams" => ["iss", $appId, $appKey, "active"]]);
            if(!empty($appInfo)) {
                return $appInfo;
            } else {
                return false;
            }
        }
        return false;
    }

}
