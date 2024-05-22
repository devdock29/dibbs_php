<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class CategoriesModel extends AppModel {

    protected $relation = 'categories';
    protected $pk = 'auto_id';
    
    public function getCatList($arr = []) {
        $searchFilters = $this->getState('catSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status = ?  ",
            "whereParams" => ["s", 'active']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND name LIKE '%" . $searchFilters['keyword'] . "%' ";
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $langData = $this->findAll($searchArr);

        return ['langs' => $langData, 'totalRecords' => $totalRecords];
    }

    public function getAllCat($arr = []) {
        $searchArr = [
            "fields" => "auto_id as id, name, description, image ",
            "whereClause" => " status = ?  ",
            "whereParams" => ["s", 'active']
        ];
        $searchArr["whereClause"] .= " ORDER BY name ASC ";
        $catData = $this->findAll($searchArr);

        return $catData;
    }
    
    public function addCategory($arr) {
        $adminUser = $this->getState("adminUser");
        $upArr = [];
        $upArr['name'] = $arr['caption'];
        $upArr['description'] = $arr['description'];
        if(!empty($arr['auto_id'])) {
            $upArr['updated_on'] = date("Y-m-d H:i:s");
            $upArr['updated_by'] = $adminUser['user_id'];
        } else {
            $upArr['added_on'] = date("Y-m-d H:i:s");
            $upArr['added_by'] = $adminUser['user_id'];
        }
        if (!empty($_FILES['image']['name'])) {
            $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
            $dir = "uploads/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777);
            }
            if (!empty($_FILES['image']['tmp_name'])) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                if (in_array($ext, $allowed)) {
                    $randId = \helpers\Common::genRandomId();
                    $bucket = \helpers\Common::genRandomString(2, 'INT');
                    if (!file_exists($dir . $bucket . "/")) {
                        mkdir($dir . $bucket . "/", 0777);
                    }
                    $imgFileName = time() . '_' . $randId;
                    $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
                        $image = $fullPath;
                        $upArr['image'] = $image;
                    }
                }
            }
        }
        $this->insert($upArr, $arr['auto_id']);
        return ['success' => "Successfully added"];
    }
}
