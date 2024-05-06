<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class FaqsController extends StateController {

    public function indexAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $oFaqsModel = new \models\FaqsModel();
        $get = $this->obtainGet();
        if(!empty($get['action']) && $get['action'] == 'delete') {
            $oFaqsModel->insert([
                "status" => "deleted",
                "updated_on" => date("Y-m-d H:i:s"),
                "updated_by" => $data['userState']['user_id'],
            ], $get['id']);
            $res['success'] = "Successfully deleted";
            $this->redirect(['url' => ADMIN_URL . "faqs", 'data' => $res]);
        }
        $offset = (!empty($get['fpn']) ? $get['fpn'] : 0);
        $perpage = (!empty($get['perPage']) ? $get['perPage'] : 20);
        $data['search'] = $this->isPOST() ? $this->obtainPost() : $this->getState('faqsSearch');
        if (!empty($get['reset']) && $get['reset'] == 'Y') {
            $this->setState('faqsSearch', '');
            $data['search'] = [];
        } else {
            $this->setState('faqsSearch', $data['search']);
        }
        $langList = $oFaqsModel->getFaqsList(['offset' => $offset, 'perpage' => $perpage]);
        $data['faqs'] = $langList['faqs'];
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

        $metaData['title'] = 'FAQs List -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }

    public function addNewAction() {
        $data = array();
        $get = $this->obtainGet();
        $oFaqsModel = new \models\FaqsModel();
        if ($this->isPOST()) {
            $POST = $this->obtainPost();
            $res = $oFaqsModel->addFaq($POST);
            if ($res) {
                $this->redirect(['url' => ADMIN_URL . "faqs", 'data' => $res]);
            }
        }

        if (!empty($get['id'])) {
            $data['caption'] = $oFaqsModel->findByPK($get['id']);
        }
        $metaData['title'] = 'Add / Update FAQs -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('add', $data);
    }

}
