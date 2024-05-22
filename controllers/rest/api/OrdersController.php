<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class OrdersController extends StateController {

    public function createAction() {
        $post = $this->obtainPost();
        $oOrdersModel = new \models\OrdersModel();
        $post['customer_id'] = $this->getAzharConfigsByKey("USER_ID");
        $retData = $oOrdersModel->createOrder($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function paymentAction() {
        $post = $this->obtainPost();
        $oOrdersModel = new \models\OrdersModel();
        $post['customer_id'] = $this->getAzharConfigsByKey("USER_ID");
        $retData = $oOrdersModel->orderPayment($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function myOrdersAction() {
        $post = $this->obtainPost();
        $oOrdersModel = new \models\OrdersModel();
        $post['customer_id'] = $this->getAzharConfigsByKey("USER_ID");
        $retData = $oOrdersModel->myOrders($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
    public function redeemAction() {
        $post = $this->obtainPost();
        $oOrdersModel = new \models\OrdersModel();
        $post['customer_id'] = $this->getAzharConfigsByKey("USER_ID");
        $retData = $oOrdersModel->redeemOrder($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
}
