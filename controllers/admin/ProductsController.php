<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class ProductsController extends StateController {

    public function indexAction() {
        $data = [];
        $data['userState'] = $this->getState('AdminUser');
        $oProductsModel = new \models\ProductsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oProductsModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => ADMIN_URL . "products", 'data' => $res]);
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
        $productList = $oProductsModel->getProductsList(['offset' => $offset, 'perpage' => $perpage]);
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
        
        $metaData['title'] = 'Manage Products on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('indexNew', $data);
    }

    public function approveAction() {
        $data = [];
        $data['userState'] = $this->getState('AdminUser');
        $oProductsModel = new \models\ProductsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && !empty($get['id'])) {
            $oProductsModel->insert([
                "status" => $get['action'],
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully updated";
            $this->redirect(['url' => ADMIN_URL . "products/approve", 'data' => $res]);
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
        $productList = $oProductsModel->getProductsList(['offset' => $offset, 'perpage' => $perpage, 'status' => 'pending']);
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
        
        $metaData['title'] = 'Approve Products on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('pending', $data);
    }
    
    public function addNewAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $get = $this->obtainGet();
        $oProductsModel = new \models\ProductsModel();
        if ($this->isPOST()) {
            $data['product'] = $POST = $this->obtainPost();
            //print_r($_FILES); print_r($POST); exit;
            $res = $oProductsModel->addNewProduct($POST);
            if ($res['status'] == "Y") {
                $data['success'] = $res['message'];
                $this->redirect(['url' => ADMIN_URL . "products", 'data' => $data]);
            } else {
                $data['error'] = $res['message'];
            }
        }

        if (!empty($get['id'])) {
            $data['product'] = $oProductsModel->getProductDetails($get['id']);
        }
        $data['product']['variations'] = !empty($data['product']['variations']) ? $data['product']['variations'] : 0;
        $oAppConfigModel = new \models\AppConfigModel();
        $data['product']['product_admin_share'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "product_admin_share", "active"]])['field_value'];
        $oCategoriesModel = new \models\CategoriesModel();
        $data['categories'] = $oCategoriesModel->getAllCat();
        
        $metaData['title'] = 'Add / Update Products -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data);
        $this->renderT('add', $data);
    }

}
