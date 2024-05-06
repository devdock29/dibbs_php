<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class LangsModel extends AppModel {

    protected $relation = 'langs';
    protected $pk = 'auto_id';
    
    public function getLangList($arr = []) {
        $searchFilters = $this->getState('langSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status = ?  ",
            "whereParams" => ["s", 'active']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND lang_en LIKE '%" . $searchFilters['keyword'] . "%' ";
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $langData = $this->findAll($searchArr);

        return ['langs' => $langData, 'totalRecords' => $totalRecords];
    }
    
    public function addLanguage($arr) {
        $adminUser = $this->getState("adminUser");
        $upArr = [];
        $upArr['type'] = $arr['type'];
        $upArr['lang_en'] = $arr['caption'];
        if(!empty($arr['auto_id'])) {
            $upArr['updated_on'] = date("Y-m-d H:i:s");
            $upArr['updated_by'] = $adminUser['user_id'];
        } else {
            $upArr['added_on'] = date("Y-m-d H:i:s");
            $upArr['added_by'] = $adminUser['user_id'];
        }
        $this->insert($upArr, $arr['auto_id']);
        return true;
    }
}
