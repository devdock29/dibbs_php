<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class PushController extends StateController {

    public function indexAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $oPushAlertsModel = new \models\PushAlertsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oPushAlertsModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => SITE_URL . "psuh", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('pushSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('pushSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('pushSearch', $data['search']);
        }
        $productList = $oPushAlertsModel->getPushList(['offset' => $offset, 'perpage' => $perpage, "user_id" => $data['userState']['user_id']]);
        $data['push'] = $productList['push'];
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
        
        $metaData['title'] = 'Manage Push on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('index', $data);
    }
    
    public function addNewAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $oPushAlertsModel = new \models\PushAlertsModel();
        if ($this->isPOST()) {
            $data['product'] = $POST = $this->obtainPost();
            $POST['user_id'] = $data['userState']['user_id'];
            $res = $oPushAlertsModel->addPushAlert($POST);
            if ($res['status'] == "Y") {
                $data['success'] = "Successfully added";
                $this->redirect(['url' => SITE_URL . "push", 'data' => $data]);
            } else {
                $data['error'] = $res['message'];
            }
        }
        
        $metaData['title'] = 'Add Push -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data);
        $this->renderT('add', $data);
    }

}
