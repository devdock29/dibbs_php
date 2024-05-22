<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class LangsController extends StateController {

    public function indexAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $oLangsModel = new \models\LangsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oLangsModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => ADMIN_URL . "langs", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('langSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('langSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('langSearch', $data['search']);
        }
        $langList = $oLangsModel->getLangList(['offset' => $offset, 'perpage' => $perpage]);
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

        $metaData['title'] = 'Language List -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }

    public function addNewAction() {
        $data = array();
        $get = $this->obtainGet();
        $oLangsModel = new \models\LangsModel();
        if ($this->isPOST()) {
            $POST = $this->obtainPost();
            $res = $oLangsModel->addLanguage($POST);
            if ($res) {
                $this->redirect(['url' => ADMIN_URL . "langs", 'data' => $res]);
            }
        }

        if (!empty($get['id'])) {
            $data['caption'] = $oLangsModel->findByPK($get['id']);
        }
        $data['caption']['type'] = !empty($data['caption']['type']) ? $data['caption']['type'] : "messages";
        $metaData['title'] = 'Add / Update Language -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('add', $data);
    }

}
