<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class DiscountCoupensModel extends AppModel {

    protected $relation = 'discount_coupens';
    protected $pk = 'auto_id';

    public function getCoupensList($arr = []) {
        $searchFilters = $this->getState('coupensSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ? ",
            "whereParams" => ["s", 'deleted']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND (code LIKE '%" . $searchFilters['keyword'] . "%' OR email LIKE '%" . $searchFilters['keyword'] . "%') ";
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $userData = $this->findAll($searchArr);

        //$oCustomersModel = new \models\CustomersModel();
        //for ($d = 0; $d < COUNT($userData); $d++) {
            //$custData = $oCustomersModel->findByPK($userData[$d]["customer_id"]);
            //$userData[$d]["custData"] = $custData;
        //}

        return ['data' => $userData, 'totalRecords' => $totalRecords];
    }
    
    public function getUserCoupens($email) {
        $retData = $this->findAll([
            "fields" => "auto_id, code, discount",
            "whereClause" => " status = ? AND ( email IS NULL OR email = ? ) ORDER BY discount DESC ",
            "whereParams" => ["ss", "active", $email]
        ]);
        return $retData;
    }
    
    public function addDiscountCoupen($params) {
        $adminUser = $this->getState("adminUser");
        $retData = ['code' => '00', 'status' => 'N'];
        $insertArr = [
            "code" => $params['code'],
            "discount" => $params['discount'],
            "email" => !empty($params['email']) ? $params['email'] : NULL,
        ];
        $insertArr['added_on'] = date("Y-m-d H:i:s");
        
        $this->insert($insertArr, $params['auto_id']);
        
        $retData["status"] = "Y";
        return $retData;
    }

}
