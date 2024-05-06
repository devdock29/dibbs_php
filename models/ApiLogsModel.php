<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class ApiLogsModel extends AppModel {

    protected $relation = 'api_logs';
    protected $pk = 'log_id';

    public function __construct() {
        $this->db()->dbConn();
    }

    public function insertAPIsLog($params) {
        $addRequestLog = $this->db()->newTable($this->relation);
        $addRequestLog->request_endpoint = !empty($params['endPoint']) ? $params['endPoint'] : $_SERVER['REQUEST_URI'];
        $addRequestLog->request_data = print_r($params['request'], true) . (!empty($_FILES) ? print_r($_FILES, true) : "");
        $addRequestLog->response_data = print_r($params['response'], true);
        $addRequestLog->request_header = print_r($params['header'], true);
        $addRequestLog->app_id = (!empty($params['appId']) ? $params['appId'] : 'NA');
        $addRequestLog->user_id = (!empty($params['userID']) ? $params['userID'] : '-');
        $addRequestLog->app_version = (isset($params['appVersion']) ? $params['appVersion'] : '');
        $addRequestLog->ip_address = !empty($params['userIp']) ? $params['userIp'] : \helpers\Common::getIP();
        $addRequestLog->request_time = !empty($params['API_START_TIME']) ? $params['API_START_TIME'] : date("Y-m-d H:i:s");
        $addRequestLog->response_time = !empty($params['API_END_TIME']) ? $params['API_END_TIME'] : NULL;
        $addRequestLog->save();
        if ($addRequestLog->isSaved()) {
            return $addRequestLog->getPK();
        } else {
            return false;
        }
    }

    public function updateAPIsLog($params, $primaryId = "") {
        $this->insert(['response_data' => print_r($params['response'], true), 'response_time' => !empty($params['API_END_TIME']) ? $params['API_END_TIME'] : date("Y-m-d H:i:s")], $primaryId);
        $addRequestLog = $this->db()->newTable($this->relation, $primaryId);
        $addRequestLog->response_data = print_r($params['response'], true);
        $addRequestLog->ip_address = !empty($params['userIp']) ? $params['userIp'] : \helpers\Common::getIP();
        $addRequestLog->response_time = !empty($params['API_END_TIME']) ? $params['API_END_TIME'] : date("Y-m-d H:i:s");
        $addRequestLog->save();
        if ($addRequestLog->isSaved()) {
            return $addRequestLog->getPK();
        } else {
            return false;
        }
    }

}
