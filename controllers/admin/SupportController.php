<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class SupportController extends StateController {

    public function indexAction() {
        $data = array();
        $get = $this->obtainGet();
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['userState'] = $this->getState('adminUser');
        $oCustomerSupportModel = new \models\CustomerSupportModel();
        if(!empty($get['action'])) {
            $oCustomerSupportModel->insert([
                "status" => $get['action'],
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully ".$get['action'];
            $this->redirect(['url' => ADMIN_URL . "support/index", 'data' => $res]);
        }
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('supportSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('supportSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('supportSearch', $data['search']);
        }

        $custList = $oCustomerSupportModel->getSupportList(['offset' => $offset, 'perpage' => $perpage]);
        $data['supports'] = $custList['data'];
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

        $metaData['title'] = 'Support Queries -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }

}
