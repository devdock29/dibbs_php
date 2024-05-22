<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class CoupensController extends StateController {

    public function indexAction() {
        $data = array();
        $get = $this->obtainGet();
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['userState'] = $this->getState('adminUser');
        $oDiscountCoupensModel = new \models\DiscountCoupensModel();
        if(!empty($get['action'])) {
            $oDiscountCoupensModel->insert([
                "status" => $get['action'],
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully ".$get['action'];
            $this->redirect(['url' => ADMIN_URL . "coupens/index", 'data' => $res]);
        }
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('coupensSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('coupensSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('coupensSearch', $data['search']);
        }

        $custList = $oDiscountCoupensModel->getCoupensList(['offset' => $offset, 'perpage' => $perpage]);
        $data['coupens'] = $custList['data'];
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

        $metaData['title'] = 'Discount Coupons -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }
    
    public function addAction() {
        $data = [];
        $data['userState'] = $this->getState('user');
        $get = $this->obtainGet();
        $oDiscountCoupensModel = new \models\DiscountCoupensModel();
        if ($this->isPOST()) {
            $data['product'] = $POST = $this->obtainPost();
            $res = $oDiscountCoupensModel->addDiscountCoupen($POST);
            if ($res['status'] == "Y") {
                $data['success'] = $res['message'];
                $this->redirect(['url' => ADMIN_URL . "coupens/index", 'data' => $data]);
            } else {
                $data['error'] = $res['message'];
            }
        }
        
        if (!empty($get['id'])) {
            $data['coupen'] = $oDiscountCoupensModel->findByPK($get['id']);
        }
        
        $metaData['title'] = 'Add Coupon -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data);
        $this->renderT('add', $data);
    }

}
