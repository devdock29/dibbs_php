<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class StoresController extends StateController {

    public function indexAction() {
        $data = array();
        $get = $this->obtainGet();
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['userState'] = $this->getState('adminUser');
        $oStoresModel = new \models\StoresModel();
        if(!empty($get['action'])) {
            $oStoresModel->insert([
                "status" => $get['action'],
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $oUsersModel = new \models\UsersModel();
            $oUsersModel->update([
                "fields" => " status = ? ",
                "whereClause" => " store_id = ? ",
                "whereParams" => ["si", $get['action'], $get['id']]
            ]);
            $oProductsModel = new \models\ProductsModel();
            $oProductsModel->update([
                "fields" => " status = ? ",
                "whereClause" => " store_id = ? ",
                "whereParams" => ["si", $get['action'], $get['id']]
            ]);
            $res['success'] = "Successfully ".$get['action'];
            $this->redirect(['url' => ADMIN_URL . "stores/index", 'data' => $res]);
        }
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('storeSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('storeSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('storeSearch', $data['search']);
        }

        $custList = $oStoresModel->getStoresList(['offset' => $offset, 'perpage' => $perpage]);
        $data['stores'] = $custList['stores'];
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

        $metaData['title'] = 'Stores List -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }

}
