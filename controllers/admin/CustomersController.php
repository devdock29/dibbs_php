<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class CustomersController extends StateController {

    public function indexAction() {
        $data = array();
        $get = $this->obtainGet();
        $data['userState'] = $this->getState('adminUser');
        $oCustomersModel = new \models\CustomersModel();
        if(!empty($get['action'])) {
            $oCustomersModel->insert([
                "status" => $get['action'],
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully ".$get['action'];
            $this->redirect(['url' => ADMIN_URL . "customers/index", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['userState'] = $this->getState('adminUser');
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('customerSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('customerSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('customerSearch',$data['search']);
        }
        
        $custList = $oCustomersModel->getUsersList(['offset' => $offset, 'perpage' => $perpage]);
        $data['users'] = $custList['users'];
        $data['totalRecords'] = $custList['totalRecords'];
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
        
        $metaData['title'] = 'Customers List -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }
    
    public function addAction() {
        $data = array();
        $get = $this->obtainGet();
        $oCustomersModel = new \models\CustomersModel();
        if ($this->isPOST()) {
            $POST = $this->obtainPost();
            $res = $oCustomersModel->updateCredits($POST);
            if ($res) {
                $this->redirect(['url' => ADMIN_URL . "customers/index", 'data' => $res]);
            }
        }

        if (!empty($get['id'])) {
            $data['caption'] = $oCustomersModel->findByPK($get['id']);
        }
        $metaData['title'] = 'Add / Update FAQs -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('add', $data);
    }

}
