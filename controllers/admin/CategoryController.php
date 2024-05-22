<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class CategoryController extends StateController {

    public function indexAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $oCategoriesModel = new \models\CategoriesModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oCategoriesModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => ADMIN_URL . "category", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('catSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('catSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('catSearch', $data['search']);
        }
        $langList = $oCategoriesModel->getCatList(['offset' => $offset, 'perpage' => $perpage]);
        $data['langs'] = $langList['langs'];
        $data['totalRecords'] = $langList['totalRecords'];
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

        $metaData['title'] = 'Categories List -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }

    public function addNewAction() {
        $data = array();
        $get = $this->obtainGet();
        $oCategoriesModel = new \models\CategoriesModel();
        if ($this->isPOST()) {
            $POST = $this->obtainPost();
            $res = $oCategoriesModel->addCategory($POST);
            if ($res) {
                $this->redirect(['url' => ADMIN_URL . "category", 'data' => $res]);
            }
        }

        if (!empty($get['id'])) {
            $data['caption'] = $oCategoriesModel->findByPK($get['id']);
        }
        $data['caption']['type'] = !empty($data['caption']['type']) ? $data['caption']['type'] : "messages";
        $metaData['title'] = 'Add / Update Category -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('add', $data);
    }

}
