<?php

namespace models;

class FaqsModel extends AppModel {

    protected $relation = 'faqs';
    protected $pk = 'auto_id';
    
    public function getFaqsList($options = []) {
        $offset = isset($options['offset']) ? $options['offset'] : 0;
        $perpage = isset($options['perpage']) ? $options['perpage'] : 20;
    
        // Assuming 'added_on' is the column where you store the timestamp
        $orderBy = isset($options['orderBy']) ? $options['orderBy'] : 'added_on';
        $orderDirection = isset($options['orderDirection']) ? $options['orderDirection'] : 'DESC';
    
        $searchFilters = $this->getState('faqsSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ? ",
            "whereParams" => ["s", "deleted"]
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND (question LIKE '%" . $searchFilters['keyword'] . "%' OR answer LIKE '%" . $searchFilters['keyword'] . "%') ";
        }
    
        $searchArr["whereClause"] .= " ORDER BY $orderBy $orderDirection LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $offset;
        $searchArr["whereParams"][] = $perpage;
    
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));
        $langData = $this->findAll($searchArr);
    
        return ['faqs' => $langData, 'totalRecords' => $totalRecords];
    }
    
    
    public function addFaq($arr) {
        $adminUser = $this->getState("adminUser");
        $upArr = [];
        $upArr['question'] = $arr['question'];
        $upArr['answer'] = $arr['answer'];
        $upArr['status'] = !empty($arr['status']) ? $arr['status'] : "active";
        if(!empty($arr['auto_id'])) {
            $upArr['updated_on'] = date("Y-m-d H:i:s");
            $upArr['updated_by'] = $adminUser['user_id'];
        } else {
            $upArr['added_on'] = date("Y-m-d H:i:s");
            $upArr['added_by'] = $adminUser['user_id'];
        }
        $this->insert($upArr, $arr['auto_id']);
        return ['success' => "FAQ successfully added"];
    }
    
    public function getList($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $offset = (!empty($params['start']) ? $params['start'] : 0);
        $rows = (!empty($params['rows']) ? $params['rows'] : 20);
        $searchArr = ["fields" => "auto_id, question, answer, status", "whereClause" => " status = ? ", "whereParams" => ["s", "active"]];
        if (!empty($params['search'])) {
            $searchArr["whereClause"] .= " AND question LIKE '%".$params['search']."%' ";
        }
        $searchArr["whereClause"] .= " ORDER BY question ASC ";
        $prodCount = $this->count($searchArr);
        $searchArr["whereClause"] .= " LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= "ii";
        $searchArr["whereParams"][] = $offset;
        $searchArr["whereParams"][] = $rows;
        $faqs = $this->findAll($searchArr);
        
        $retData["code"] = "11";
        $retData["data"]['faqs'] = $faqs;
        $retData["data"]['count'] = $prodCount;
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("25", "lang_en")["lang_en"];
        return $retData;
    }
}
