<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class ProductsController extends StateController {

    public function indexAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $oProductsModel = new \models\ProductsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oProductsModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => SITE_URL . "products", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('productSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('productSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('productSearch', $data['search']);
        }
        $productList = $oProductsModel->getProductsList(['offset' => $offset, 'perpage' => $perpage, 'store_id' => $data['userState']['store_id']]);
        $data['products'] = $productList['products'];
        $data['totalRecords'] = $productList['totalRecords'];
        $paginateSettings = [
            'url' => $this->url(),
            'baseURL' => $this->baseURL(),
            'getParams' => $this->obtainGet()
        ];
        $paginateData = ['offset' => $offset,
            'totalRecords' => $data['totalRecords'],
            'perPage' => $perpage
        ];
        $oPaginate = $this->paginate($paginateSettings);
        $data['pagenation'] = $oPaginate->getPaging($paginateData);
        $paginateData['messageOnly'] = 'Y';
        $paginateData['displayNote'] = 'All';
        $data['pagenationInfo'] = $oPaginate->getPaging($paginateData);
        $data['offset'] = $offset;
        $data['perpage'] = $perpage;
        
        $metaData['title'] = 'Manage Your Products on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('indexNew', $data);
    }
    
    public function addNewAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $get = $this->obtainGet();
        $oProductsModel = new \models\ProductsModel();
        if ($this->isPOST()) {
            $data['product'] = $POST = $this->obtainPost();
            $POST['store_id'] = $this->getAzharConfigsByKey("STORE_ID");
            $res = $oProductsModel->addNewProduct($POST);
            if ($res['status'] == "Y") {
                $data['success'] = $res['message'];
                $this->redirect(['url' => SITE_URL . "products", 'data' => $data]);
            } else {
                $data['error'] = $res['message'];
            }
        } else {
            if (!empty($get['id'])) {
                $data['product'] = $oProductsModel->getProductDetails($get['id']);
            }
        }
        $data['can_add_product'] = "Y";
        $store_id = $this->getAzharConfigsByKey("STORE_ID");
        $oStoresModel = new \models\StoresModel();
        $storeInfo = $oStoresModel->getStoreProfile($store_id);
        if(empty($storeInfo['address']) || empty($storeInfo['phone']) || empty($storeInfo['redeem_code']) || empty($storeInfo['store_timings'])) {
            $data['can_add_product'] = "N";
            $data['error_message'] = "<b>Your Profile is Incomplete.<b><br />Please complete your profile first to add the product to your store.<br/><br/>"
                    . "<ul>"
                    . "<li>Enter Business Address</li>"
                    . "<li>Enter Business Phone no.</li>"
                    . "<li>Enter Business Hours</li>"
                    . "<li>Enter Redeem Code to Redeem the Orders</li>"
                    . "</ul>";
        }
        $data['product']['variations'] = $data['product']['variations'] > 0 ? $data['product']['variations'] : 0;
        if(empty($data['product']['owner_share'])) {
            $oAppConfigModel = new \models\AppConfigModel();
            $data['product']['owner_share'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "product_admin_share", "active"]])['field_value'];
            $data['product']['owner_share'] = $data['product']['owner_share'] > 0 ? $data['product']['owner_share'] : 3;
        }
        $oCategoriesModel = new \models\CategoriesModel();
        $data['categories'] = $oCategoriesModel->getAllCat();
        
        $metaData['title'] = 'Add / Update Products -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data);
        $this->renderT('add', $data);
    }

}
