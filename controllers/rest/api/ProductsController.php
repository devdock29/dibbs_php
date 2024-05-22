<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class ProductsController extends AppController {

    public function searchAction() {
        $this->authRequest();
        $post = $this->obtainPost();
        $oProductsModel = new \models\ProductsModel();
        $retData = $oProductsModel->searchProducts($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function saveAction() {
        $this->authRequest();
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->saveProduct($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function myProductsAction() {
        $this->authRequest();
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->getMyProductsList($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function delMyProductAction() {
        $this->authRequest();
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->delMyProduct($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }

    public function categoriesAction() {
        $post = $this->obtainPost();
        $oCategoriesModel = new \models\CategoriesModel();
        $retData['data'] = $oCategoriesModel->getAllCat();

        $this->response = $retData;
        echo $this->buildResponse($retData['status'] ?: "Y", $this->response, $retData['code'] ?: '11');
    }

    private function authRequest() {
        $header = $this->obtainHeaders();
        $postData = $this->obtainPost();
        $oApiLogsModel = new \models\ApiLogsModel();

        $this->appKey = (!empty($header['appKey']) ? $header['appKey'] : $header['Appkey']);
        $this->appId = (!empty($header['appId']) ? $header['appId'] : $header['Appid']);
        $oMobAppSettingsModel = new \models\MobAppSettingsModel();
        $appInfo = $oMobAppSettingsModel->getApiAuthSettings($this->appId, $this->appKey);

        if (!empty($header['Token']) || !empty($postData['token']) || !empty($header['token'])) {
            $oCustomersTokenModel = new \models\CustomersTokenModel();
            $userId = $oCustomersTokenModel->validateToken(!empty($postData['token']) ? $postData['token'] : (!empty($header['token']) ? $header['token'] : $header['Token']))['user_id'];
            if (!empty($userId)) {
            } else {
                header('Content-Type: application/json; charset=utf8');
                header('HTTP/1.1 401 Unauthorised');
                $data["code"] = "00";
                $data["msg"] = "Login Required.";
                $allRequest = [];
                $allRequest['response'] = $data;
                $allRequest['request'] = $postData;
                $allRequest['header'] = $header;
                $allRequest['appId'] = $appInfo['app_id'];
                $allRequest['userID'] = $postData['token'];
                $allRequest['appVersion'] = $header['appversion'];
                $allRequest['API_START_TIME'] = date("Y-m-d H:i:s");
                $allRequest['API_END_TIME'] = date("Y-m-d H:i:s");
                $requestLogId = $oApiLogsModel->insertAPIsLog($allRequest);
                echo $this->buildResponse("N", $data, '00');
                exit;
            }
        } else {
            header('Content-Type: application/json; charset=utf8');
            header('HTTP/1.1 401 Unauthorised');
            $data["code"] = "00";
            $data["msg"] = "Login Required";
            $allRequest = [];
            $allRequest['response'] = $data;
            $allRequest['request'] = $postData;
            $allRequest['header'] = $header;
            $allRequest['appId'] = $appInfo['app_id'];
            $allRequest['userID'] = $postData['token'];
            $allRequest['appVersion'] = $header['appversion'];
            $allRequest['API_START_TIME'] = date("Y-m-d H:i:s");
            $allRequest['API_END_TIME'] = date("Y-m-d H:i:s");
            $requestLogId = $oApiLogsModel->insertAPIsLog($allRequest);
            echo $this->buildResponse("N", $data, '00');
            exit;
        }
    }
    
}
