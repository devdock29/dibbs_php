<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class ProductsController extends StateController {

    public function searchAction() {
        $post = $this->obtainPost();
        $oProductsModel = new \models\ProductsModel();
        $retData = $oProductsModel->searchProducts($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function saveAction() {
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->saveProduct($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function myProductsAction() {
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->getMyProductsList($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function delMyProductAction() {
        $post = $this->obtainPost();
        $oCustomerProductsModel = new \models\CustomerProductsModel();
        $retData = $oCustomerProductsModel->delMyProduct($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
}
