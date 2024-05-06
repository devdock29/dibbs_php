<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class AppController extends \controllers\AppController {

    private $appKey;
    private $appId;
    protected $header = array();
    protected $response;
    protected $storeID = 0;
    protected $partnerID = 0;
    protected $uid = 0;
    protected $userInfo = array();

    public function __construct() {
        //$this->db()->dbConn();
    }

    public function beforeAction() {
        $header = $this->obtainHeaders();
        $getData = $this->obtainGet();
        $postData = $this->obtainPost();

        $this->appKey = (!empty($header['appKey']) ? $header['appKey'] : $header['Appkey']);
        $this->appId = (!empty($header['appId']) ? $header['appId'] : $header['Appid']);
        $api_version = (isset($header['api_version']) ? $header['api_version'] : '1');
        $this->addToConfig("API_VERSION", $api_version);
        $lang = (isset($header['lang']) && in_array($header['lang'], ['en', 'ur', 'rur']) ? $header['lang'] : 'en');
        $this->addToConfig("APP_LANG", $lang);
        $this->addToConfig("APP_VERSION", $header['appversion']);
        $start = (isset($getData['start']) && $getData['start'] > 0 ? $getData['start'] : 0);
        $end = (isset($getData['end']) && $getData['end'] > 0 ? $getData['end'] : 10);
        $rows = (isset($getData['rows']) && $getData['rows'] > 0 ? $getData['rows'] : 10);
        $oMobAppSettingsModel = new \models\MobAppSettingsModel();
        $appInfo = $oMobAppSettingsModel->getApiAuthSettings($this->appId, $this->appKey);

        if (!$appInfo['app_id']) {
            header('Content-Type: application/json; charset=utf8');
            header('HTTP/1.1 401 Unauthorised');
            $data["code"] = "00";
            $data["msg"] = "Not authorized";
            $oApiLogsModel = new \models\ApiLogsModel();
            $allRequest = [];
            $allRequest['response'] = $data;
            $allRequest['request'] = $postData;
            $allRequest['header'] = $header;
            $allRequest['reponse'] = $data;
            $allRequest['appId'] = $appInfo['app_id'];
            $allRequest['userID'] = $postData['token'];
            $allRequest['appVersion'] = $header['appversion'];
            $allRequest['API_START_TIME'] = date("Y-m-d H:i:s");
            $allRequest['API_END_TIME'] = date("Y-m-d H:i:s");
            $requestLogId = $oApiLogsModel->insertAPIsLog($allRequest);
            echo $this->buildResponse("N", $data, '00');
            exit;
        } else {
            if (!empty($header['token']) || !empty($postData['token'])) {
                $oCustomersTokenModel = new \models\CustomersTokenModel();
                $userId = $oCustomersTokenModel->validateToken(!empty($postData['token']) ? $postData['token'] : $header['token'])['user_id'];
                if (!empty($userId)) {
                    $this->uid = $userId;
                    $this->addToConfig("USER_ID", $userId);
                } 
            }
            $this->limit = "LIMIT " . $start . "," . $end;
            $this->start = ($start > 0 ? $start : (isset($header['start']) && $header['start'] > 0 ? $header['start'] : 0));
            $this->end = ($end > 0 ? $end : ($header['rows'] > 0 ? $header['rows'] : 10));
            $this->rows = ($rows > 0 ? $rows : ($header['rows'] > 0 ? $header['rows'] : 10));

            $this->addToConfig("APP_PLATFORM", $appInfo['app_platform']);
            $this->addToConfig("DEVICE_OS", $header['deviceos']);

            if (is_array($getData)) {
                $postData = array_merge($postData, $getData);
            }
            $oApiLogsModel = new \models\ApiLogsModel();
            $allRequest = [];
            $allRequest['response'] = "";
            $allRequest['request'] = $postData;
            $allRequest['header'] = $header;
            $allRequest['appId'] = $appInfo['app_id'];
            $allRequest['userID'] = $userId;
            $allRequest['appVersion'] = $header['appversion'];
            $allRequest['API_START_TIME'] = date("Y-m-d H:i:s");
            $requestLogId = $oApiLogsModel->insertAPIsLog($allRequest);
            $this->addToConfig("API_START_LOG_ID", $requestLogId);
        }
        $retData = ['code' => "00"];
    }

    public function afterAction() {    
        $oApiLogsModel = new \models\ApiLogsModel();
        $allRequest = $this->isGet() ? $this->obtainGet() : array();
        $allRequest['API_END_TIME'] = date("Y-m-d H:i:s");
        $allRequest['response'] = $this->response;
        $oApiLogsModel->updateAPIsLog($allRequest, !empty($this->getAzharConfigsByKey("API_START_LOG_ID")) ? $this->getAzharConfigsByKey("API_START_LOG_ID") : NULL);
    }

}
