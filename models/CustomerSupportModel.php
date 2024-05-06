<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class CustomerSupportModel extends AppModel {

    protected $relation = 'customers_support';
    protected $pk = 'auto_id';

    public function getSupportList($arr = []) {
        $searchFilters = $this->getState('supportSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ? ",
            "whereParams" => ["s", 'deleted']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND (email LIKE '%" . $searchFilters['keyword'] . "%' OR message LIKE '%" . $searchFilters['keyword'] . "%') ";
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $userData = $this->findAll($searchArr);

        $oCustomersModel = new \models\CustomersModel();
        for ($d = 0; $d < COUNT($userData); $d++) {
            $custData = $oCustomersModel->findByPK($userData[$d]["customer_id"]);
            $userData[$d]["custData"] = $custData;
        }

        return ['data' => $userData, 'totalRecords' => $totalRecords];
    }

}
