<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class LoginController extends AppController {

    public function registerAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->register($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function loginAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->login($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function forgotPasswordAction() {
        $post = $this->obtainPost();
        $oCustomersModel = new \models\CustomersModel();
        $retData = $oCustomersModel->forgotPassword($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function searchProductsAction() {
        $post = $this->obtainPost();
        $oProductsModel = new \models\ProductsModel();
        $retData = $oProductsModel->searchProducts($post);

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

}
