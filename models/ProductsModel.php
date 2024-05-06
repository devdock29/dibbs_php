<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class ProductsModel extends AppModel {

    protected $relation = 'products';
    protected $pk = 'product_id';

    public function getProductsList($arr = []) {
        $searchFilters = $this->getState('productSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ? ",
            "whereParams" => ["s", 'deleted']
        ];
        if (!empty($searchFilters['store_id'])) {
            $searchArr["whereClause"] .= " AND store_id = ? ";
            $searchArr["whereParams"][0] .= "i";
            $searchArr["whereParams"][] = $searchFilters['store_id'];
        } elseif (!empty($arr['store_id'])) {
            $searchArr["whereClause"] .= " AND store_id = ? ";
            $searchArr["whereParams"][0] .= "i";
            $searchArr["whereParams"][] = $arr['store_id'];
        }
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND (product_name LIKE '%" . $searchFilters['keyword'] . "%' OR description LIKE '%" . $searchFilters['keyword'] . "%') ";
        }
        /* if (!empty($searchFilters['type'])) {
          $searchArr["whereClause"] .= " AND type = ? ";
          $searchArr["whereParams"][0] .= "s";
          $searchArr["whereParams"][] = $searchFilters['type'];
          } */
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $userData = $this->findAll($searchArr);

        $oProductVariationsModel = new \models\ProductVariationsModel();
        $oProductImagesModel = new \models\ProductImagesModel();
        $oStoresModel = new \models\StoresModel();
        for ($d = 0; $d < COUNT($userData); $d++) {
            $variations = $oProductVariationsModel->findAll([
                "fields" => "name, image, price, discount, added_on, tags",
                "whereClause" => " product_id = ? AND status = ? ",
                "whereParams" => ["is", $userData[$d]["product_id"], "active"],
            ]);
            $userData[$d]["variations"] = count($variations);
            $userData[$d]["images"] = $oProductImagesModel->findAll(["fields" => "image ", "whereClause" => "product_id = ? AND status = ? ", "whereParams" => ["is", $product_id,  "active"]]);
            $userData[$d]["varData"] = $variations;
            $userData[$d]["storeData"] = $oStoresModel->findByPK($userData[$d]['store_id'], "store_name");
        }

        return ['products' => $userData, 'totalRecords' => $totalRecords];
    }

    public function addNewProduct($params) {
        $adminUser = $this->getState("user");
        if(empty($adminUser)) {
            $adminUser = $this->getState("adminUser");
        }
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['product_name'])) {
            $retData["message"] = $oLangsModel->findByPK("1", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['description'])) {
            $retData["message"] = $oLangsModel->findByPK("1", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['price'])) {
            $retData["message"] = $oLangsModel->findByPK("2", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['end_date'])) {
            $retData["message"] = $oLangsModel->findByPK("31", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['end_time'])) {
            $retData["message"] = $oLangsModel->findByPK("32", "lang_en")["lang_en"];
            return $retData;
        }
        $image = "";
        /* if (!empty($_FILES['image']['name'])) {
          $image = \helpers\Common::uploadImage($_FILES, 'image');
          } */
        $insertArr = [
            "product_name" => $params['product_name'],
            "description" => $params['description'],
            "price" => $params['price'],
            "image" => !empty($image) ? $image : NULL,
            "discount" => $params['discount'],
            "tags" => $params['tags'],
            "status" => !empty($params['status']) ? $params['status'] : "active",
            "end_date" => date("Y-m-d", strtotime($params['end_date'])),
            "end_time" => date("H:i:s", strtotime($params['end_time'])),
            "end_date_time" => date("Y-m-d", strtotime($params['end_date']))." ".date("H:i:s", strtotime($params['end_time'])),
            "purchase_valid" => !empty($params['purchase']) ? $params['purchase'] : NULL,
            "appointment" => !empty($params['appointment']) ? $params['appointment'] : NULL,
            "per_person_purchase" => !empty($params['purchase_limit']) ? $params['purchase_limit'] : NULL,
            "return_policy" => !empty($params['return_policy']) ? $params['return_policy'] : NULL,
        ];
        if (!empty($params['owner_share'])) {
            $insertArr['owner_share'] = $params['owner_share'];
        } 
        if (!empty($params['product_id'])) {
            $insertArr['updated_on'] = date("Y-m-d H:i:s");
            $insertArr['updated_by'] = $adminUser['user_id'];
        } else {
            $insertArr['added_on'] = date("Y-m-d H:i:s");
            $insertArr['added_by'] = $adminUser['user_id'];
            $insertArr['ip_address'] = \helpers\Common::getIP();
            $insertArr['store_id'] = $params['store_id'];
        }
        $product_id = $this->insert($insertArr, $params['product_id']);
        if (!empty($product_id)) {
            $oProductVariationsModel = new \models\ProductVariationsModel();
            //$oProductVariationsModel->update(["fields" => "status = ?, updated_on = ?, updated_by = ? ", "whereClause" => "product_id = ? AND status = ? ", "whereParams" => ["ssiis", "deleted", date("Y-m-d H:i:s"), $adminUser['user_id'], $product_id, "active"]]);
            $extImage = $varIds = "";
            if ($params['variations'] > 0) {
                for ($v = 0; $v < $params['variations']; $v++) {
                    $image = "";
                    if (!empty($params['var_name'][$v]) && !empty($params['price'][$v])) {
                        if (!empty($_FILES['image']['name'][$v])) {
                            $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
                            $dir = "uploads/";
                            if (!file_exists($dir)) {
                                mkdir($dir, 0777);
                            }
                            if (!empty($_FILES['image']['tmp_name'][$v])) {
                                $ext = pathinfo($_FILES['image']['name'][$v], PATHINFO_EXTENSION);
                                if (in_array($ext, $allowed)) {
                                    $randId = \helpers\Common::genRandomId();
                                    $bucket = \helpers\Common::genRandomString(2, 'INT');
                                    if (!file_exists($dir . $bucket . "/")) {
                                        mkdir($dir . $bucket . "/", 0777);
                                    }
                                    $imgFileName = time() . '_' . $randId;
                                    $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
                                    if (move_uploaded_file($_FILES['image']['tmp_name'][$v], $fullPath)) {
                                        $image = $fullPath;
                                    }
                                }
                            }
                        }
                        $extImage = empty($extImage) && !empty($image) ? $image : "";
                        $insertArrV = [
                            "product_id" => $product_id,
                            "name" => $params['var_name'][$v],
                            "price" => $params['price'][$v],
                            "image" => !empty($image) ? $image : NULL,
                            "discount" => $params['discount'][$v],
                            "tags" => $params['tags'][$v],
                        ];
                        if (empty($params['auto_id'][$v])) {
                            $insertArrV['added_on'] = date("Y-m-d H:i:s");
                            $insertArrV['added_by'] = $adminUser['user_id'];
                        } else {
                            $insertArrV['updated_on'] = date("Y-m-d H:i:s");
                            $insertArrV['updated_by'] = $adminUser['user_id'];
                        }
                        $id = $oProductVariationsModel->insert($insertArrV, $params['auto_id'][$v]);
                        if (empty($params['auto_id'][$v])) {
                            $varIds .= $id . ",";
                        } else {
                            $varIds .= $params['auto_id'][$v] . ",";
                        }
                    }
                }
                if (!empty($varIds)) {
                    $varIds = rtrim($varIds, ",");
                    $oProductVariationsModel->update(["fields" => "status = ?, updated_on = ?, updated_by = ? ", "whereClause" => "product_id = ? AND status = ? AND auto_id NOT IN ($varIds) ", "whereParams" => ["ssiis", "deleted", date("Y-m-d H:i:s"), $adminUser['user_id'], $product_id, "active"]]);
                }
                if (!empty($extImage)) {
                    $this->insert([
                        'image' => $extImage
                            ], $product_id);
                }
                //print_r($_FILES['prod_image']); exit;
                if(COUNT($_FILES['prod_image']) > 0) {
                    $oProductImagesModel = new \models\ProductImagesModel();
                    $extQry = "";
                    $preImages = implode(",", $params['pre_images']);
                    if(!empty($preImages)) {
                        $extQry = " AND auto_id NOT IN ($preImages) ";
                    }
                    $oProductImagesModel->update(["fields" => "status = ? ", "whereClause" => "product_id = ? AND status = ? $extQry ", "whereParams" => ["sis", "deleted", $product_id,  "active"]]);
                    for($i = 0; $i < COUNT($_FILES['prod_image']); $i++) {
                        if (!empty($_FILES['prod_image']['name'][$i])) {
                            $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
                            $dir = "uploads/";
                            if (!file_exists($dir)) {
                                mkdir($dir, 0777);
                            }
                            if (!empty($_FILES['prod_image']['tmp_name'][$i])) {
                                $ext = pathinfo($_FILES['prod_image']['name'][$i], PATHINFO_EXTENSION);
                                if (in_array($ext, $allowed)) {
                                    $randId = \helpers\Common::genRandomId();
                                    $bucket = \helpers\Common::genRandomString(2, 'INT');
                                    if (!file_exists($dir . $bucket . "/")) {
                                        mkdir($dir . $bucket . "/", 0777);
                                    }
                                    $imgFileName = time() . '_' . $randId;
                                    $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
                                    if (move_uploaded_file($_FILES['prod_image']['tmp_name'][$i], $fullPath)) {
                                        $image = $fullPath;
                                        $oProductImagesModel->insert([
                                            "product_id" => $product_id,
                                            "image" => $fullPath,
                                            "added_on" => date("Y-m-d H:i:s"),
                                            "added_by" => $product_id,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $retData["code"] = "11";
        $retData["data"] = $product_id;
        $retData["status"] = "Y";
        $retData["message"] = "Your listing has been updated.";//$oLangsModel->findByPK("6", "lang_en")["lang_en"];
        return $retData;
    }

    public function getProductDetails($product_id) {
        $prod_data = $this->findByPK($product_id, "product_id, store_id, product_name, description, price, discount, owner_share, image, added_on, status, store_id, end_date_time, end_date, end_time, purchase_valid, appointment, per_person_purchase, return_policy ");
        $oProductVariationsModel = new \models\ProductVariationsModel();
        $variations = $oProductVariationsModel->findAll([
            "fields" => "auto_id, name, image, price, discount, added_on, tags",
            "whereClause" => " product_id = ? AND status = ? ",
            "whereParams" => ["is", $product_id, "active"],
        ]);
        $prod_data['price'] = $variations[0]['price'] > 0 ? $variations[0]['price'] : 0;
        $prod_data['discount'] = $variations[0]['discount'] > 0 ? $variations[0]['discount'] : 0;
        $oProductImagesModel = new \models\ProductImagesModel();
        $prod_data["images"] = $oProductImagesModel->findAll(["fields" => "auto_id, image ", "whereClause" => "product_id = ? AND status = ? ", "whereParams" => ["is", $product_id,  "active"]]);
        /*$prod_data["images_old"] = "";
        for($im = 0; $im < COUNT($prod_data["images"]); $im++) {
            $prod_data["images_old"] .= $prod_data["images"][$im]['auto_id'].",";
        }*/
        $prod_data["images_old"] = rtrim($prod_data["images_old"], ",");
        $prod_data["variations"] = count($variations);
        $prod_data["varData"] = $variations;
        $oStoresModel = new \models\StoresModel();
        //$prod_data["store_info"] = $oStoresModel->findByPK($prod_data['store_id'], "store_name, phone, website, start_time, end_time, location");
        $prod_data["store_info"] = $oStoresModel->getStoreProfile($prod_data['store_id']);
        return $prod_data;
    }

    public function searchProducts($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $offset = (!empty($params['start']) ? $params['start'] : 0);
        $rows = (!empty($params['rows']) ? $params['rows'] : 20);
        $searchArr = ["fields" => "product_id", "whereClause" => " status = ? AND end_date_time > ?", "whereParams" => ["ss", "active", date("Y-m-d H:i:s")]];
        if (!empty($params['keywords'])) {
            $searchArr["whereClause"] .= " AND (product_name LIKE '%" . $params['keywords'] . "%' OR description LIKE '%" . $params['keywords'] . "%' OR tags LIKE '%" . $params['keywords'] . "%') ";
        }
        $searchArr["whereClause"] .= " ORDER BY end_date_time ASC ";
        $prodCount = $this->count($searchArr);
        $searchArr["whereClause"] .= " LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= "ii";
        $searchArr["whereParams"][] = $offset;
        $searchArr["whereParams"][] = $rows;
        $prodData = $this->findAll($searchArr);
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $loggedUserId = $this->getAzharConfigsByKey("USER_ID");
        for ($p = 0; $p < COUNT($prodData); $p++) {
            $prodData[$p] = $this->getProductDetails($prodData[$p]['product_id']);
            $prodData[$p]['mySavedProduct'] = "N";
            
            if (!empty($loggedUserId)) {
                $custProduct = $oCustomerProductsModel->find(["fields" => "product_id", "whereClause" => "customer_id = ? AND product_id = ? AND status = ?", "whereParams" => ["iis", $loggedUserId, $prodData[$p]['product_id'], "active"]]);
                if (!empty($custProduct)) {
                    $prodData[$p]['mySavedProduct'] = "Y";
                }
            }
        }
        if (!empty($prodData)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["data"]['products'] = $prodData;
            $retData["data"]['count'] = $prodCount;
            $oAppConfigModel = new \models\AppConfigModel();
            $retData["data"]['coupen_code'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_code", "active"]])['field_value'];
            $retData["data"]['coupen_amount'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_amount", "active"]])['field_value'];
            if(!empty($this->getAzharConfigsByKey("USER_ID"))) {
                $oCustomersModel = new \models\CustomersModel();
                $custInfo = $oCustomersModel->findByPK($this->getAzharConfigsByKey("USER_ID"), "credits, email");
                $retData["data"]['dibbs_credits'] = $custInfo['credits'];
            }
            $oDiscountCoupensModel = new \models\DiscountCoupensModel();
            $coupens = $oDiscountCoupensModel->getUserCoupens($custInfo['email']);
            $retData["data"]['coupens'] = $coupens;
            $retData["message"] = $oLangsModel->findByPK("13", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("12", "lang_en")["lang_en"];
        }
        return $retData;
    }

}
