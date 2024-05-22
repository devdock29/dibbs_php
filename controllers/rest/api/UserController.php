<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class UserController extends StateController {

    public function logoutAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->logout($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function updateProfileSettingAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->updateProfileSetting($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function supportAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->support($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function updateSupportAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->updateSupport($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function addReferralAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->addRefferal($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
}
