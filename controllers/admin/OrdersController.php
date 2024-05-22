<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class OrdersController extends StateController {

    public function indexAction() {
        $data = [];
        $data['userState'] = $this->getState('adminUser');
        $oOrdersModel = new \models\OrdersModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'cancelled') {
            $oOrdersModel->insert([
                "status" => "cancelled",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Order Successfully Cancelled";
            $orderInfo = $oOrdersModel->findByPK($get['id']);
            $oStoresModel = new \models\StoresModel();
            $storeData = $oStoresModel->findByPK($orderInfo['store_id']);
            $oUsersModel = new \models\UsersModel();
            $storeOwnerData = $oUsersModel->findByPK($storeData['user_id']);
            $oCustomersModel = new \models\CustomersModel();
            $customerData = $oCustomersModel->findByPK($orderInfo['customer_id']);
            $oAppConfigModel = new \models\AppConfigModel();
            $support_email = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]])['field_value'];
            $mailParams = [
                "emailAddress" => $storeOwnerData['email'],
                "subject" => "Your order (".$get['id'].") on ".DOMAIN_NAME . " has been Cancelled ",
                "message" => "<b>Dear ".$storeOwnerData['user_name'].",</b><br/><br/>"
                . "We have gone ahead and canceled your Dibbs Deal and we're sorry to hear things didn't work out this time around.<br/><br/>"
                . "<b>Order Id</b>: ".$get['id']."<br/>"
                . "<b>Amount Refunded</b>: " . $orderInfo['owner_amount'] . "<br/><br/>"
                . "Please allow 3-5 business days for the amount to be refunded to your original payment method.<br/><br/>"
                . "<b>Thank You</b><br/>"
                . "The ".DOMAIN_NAME." Team",
                "cc" => "",
                "bcc" => $support_email.",",
                "replyTo" => $support_email,
            ];
            \helpers\Common::sendMail($mailParams);
            $this->redirect(['url' => ADMIN_URL . "orders", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('orderSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('orderSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('orderSearch', $data['search']);
        }
        $orderSearch = ['offset' => $offset, 'perpage' => $perpage];
        if(!empty($data['search'])) {
            $orderSearch['customer_id'] = !empty($data['search']['customer_id']) ? $data['search']['customer_id'] : "";
            $orderSearch['status'] = !empty($data['search']['status']) ? $data['search']['status'] : "";
            $orderSearch['store_id'] = !empty($data['search']['store_id']) ? $data['search']['store_id'] : "";
        }
        $ordersList = $oOrdersModel->getOrdersList($orderSearch);
        $data['orders'] = $ordersList['orders'];
        $data['totalRecords'] = $ordersList['totalRecords'];
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
        
        $metaData['title'] = 'Manage Orders on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('indexNew', $data);
    }

}
