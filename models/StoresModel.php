<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class StoresModel extends AppModel {
    protected $relation = 'stores';
    protected $pk = 'store_id';
    
    public function getStoresList($arr = []) {
        $searchFilters = $this->getState('storeSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ?  ",
            "whereParams" => ["s", 'deleted']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND store_name LIKE '%" . $searchFilters['keyword'] . "%' ";
        }
        /*if (!empty($searchFilters['type'])) {
            $searchArr["whereClause"] .= " AND type = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = $searchFilters['type'];
        }*/
        $totalRecords = $this->count(array("fields" => " COUNT(".$this->pk.")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $storeData = $this->findAll($searchArr);

        $oUsersModel = new \models\UsersModel();
        for ($d = 0; $d < COUNT($storeData); $d++) {
            $userId = $storeData[$d]['user_id'];
            $storeData[$d]['userInfo'] = $oUsersModel->findByPK($userId);
        }

        return ['stores' => $storeData, 'totalRecords' => $totalRecords];
    }
    
    public function getStoreProfile($store_id) {
        $store_data = $this->findByPK($store_id);
        $oStoreTimingsModel = new \models\StoreTimingsModel();
        $store_data['store_timings'] = $oStoreTimingsModel->findByFieldInt("store_id", $store_id);
        return $store_data;
    }
}
