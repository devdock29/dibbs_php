<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class CustomerProductsModel extends AppModel {

    protected $relation = 'customers_products';
    protected $pk = 'auto_id';

    public function saveProduct($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['product_id'])) {
            $retData["message"] = $oLangsModel->findByPK("15", "lang_en")["lang_en"];
            return $retData;
        }
        if (!empty($this->checkUserProduct($params['product_id']))) {
            $retData["message"] = $oLangsModel->findByPK("16", "lang_en")["lang_en"];
            return $retData;
        }
        $save_id = $this->insert([
            "customer_id" => $this->getAzharConfigsByKey("USER_ID"),
            "product_id" => $params['product_id'],
            "added_on" => date("Y-m-d H:i:s"),
            "added_by" => $this->getAzharConfigsByKey("USER_ID"),
        ]);
        $retData["code"] = "11";
        $retData["data"] = $save_id;
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("17", "lang_en")["lang_en"];
        return $retData;
    }
    
    public function delMyProduct($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['product_id'])) {
            $retData["message"] = $oLangsModel->findByPK("15", "lang_en")["lang_en"];
            return $retData;
        }
        $myProd = $this->checkUserProduct($params['product_id']);
        if (empty($myProd)) {
            $retData["message"] = $oLangsModel->findByPK("18", "lang_en")["lang_en"];
            return $retData;
        }
        $this->insert([
            "status" => "deleted",
            "updated_on" => date("Y-m-d H:i:s"),
        ], $myProd['auto_id']);
        $retData["code"] = "11";
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("19", "lang_en")["lang_en"];
        return $retData;
    }
    
    public function getMyProductsList($arr = []) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $start = (!empty($arr['start']) ? $arr['start'] : 0);
        $rows = (!empty($arr['rows']) ? $arr['rows'] : 20);
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status = ? AND customer_id = ? ",
            "whereParams" => ["si", 'active', $this->getAzharConfigsByKey("USER_ID")]
        ];
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $start;
        $searchArr["whereParams"][] = $rows;
        $proData = $this->findAll($searchArr);

        $oProductsModel = new \models\ProductsModel();
        for ($p = 0; $p < COUNT($proData); $p++) {
            $prodDetails = $oProductsModel->getProductDetails($proData[$p]['product_id']);
            $proData[$p]["prodData"] = $prodDetails;
        }
        $retData["code"] = "11";
        $retData["data"]["products"] = $proData;
        $retData["data"]["count"] = $totalRecords;
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("13", "lang_en")["lang_en"];
        return $retData;
    }

    public function checkUserProduct($product_id) {
        return $this->find(["fields" => "*", "whereClause" => "customer_id = ? AND product_id = ? AND status = ?", "whereParams" => ["iis", $this->getAzharConfigsByKey("USER_ID"), $product_id, "active"]]);
    }

}
