<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class PushAlertsModel extends AppModel {

    protected $relation = 'push_alerts';
    protected $pk = 'auto_id';
    
    public function getPushList($arr = []) {
        $searchFilters = $this->getState('pushSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ? ",
            "whereParams" => ["s", 'deleted']
        ];
        if(!empty($arr['user_id'])) {
            $searchArr["whereClause"] .= " AND added_by = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = "ad.".$arr['user_id'];
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $pushData = $this->findAll($searchArr);

        return ['push' => $pushData, 'totalRecords' => $totalRecords];
    }
    
    public function addPushAlert($params) {
        $adminUser = $this->getState("adminUser");
        $retData = ['code' => '00', 'status' => 'N'];
        $insertArr = [
            "audiance" => $params['audiance'],
            "customer_id" => !empty($params['customer_id']) ? $params['customer_id'] : NULL,
            "heading" => $params['heading'],
            "message" => $params['message'],
        ];
        $insertArr['added_on'] = date("Y-m-d H:i:s");
        $insertArr['added_by'] = !empty($params['user_id']) ? "ad.".$params['user_id'] : $adminUser['user_id'];
        
        $this->insert($insertArr);
        
        $retData["status"] = "Y";
        return $retData;
    }
    
}
