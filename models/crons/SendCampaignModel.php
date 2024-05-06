<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models\crons;

class SendCampaignModel extends \models\crons\CronModel {

    protected $relation = 'push_alerts';
    protected $pk = 'auto_id';

    public function __construct() {
        $this->whereClause = 'status = ? ';
        $this->whereParams = ['s', 'pending'];
        $this->setIterationSize(20);
    }

    public function build() {
        $this->limit();
        $data = $this->findAll([
            'fields' => '*',
            'whereClause' => $this->whereClause,
            'whereParams' => $this->whereParams
        ]);
        if ($data !== null) {
            foreach ($data as $row) {
                $this->manipulateRow($row);
            }
        }
    }

    public function manipulateRow($row) {
        //print_r($row);
        $updateId = $row['auto_id'];
        $audiance = $row['audiance'];
        $customer = $row['customer_id'];
        $heading = $row['heading'];
        $message = $row['message'];
        if ($audiance == 'all') {
            /*$oCustomersGcmModel = new \models\UsersGcmModel();
            $users = $oCustomersGcmModel->findAll(["fields" => "*", "whereClause" => "1", "whereParams" => [""]]);*/
            $sendArr = array();
    		$sendArr['to'] = "/topics/AllUsers";
            $sendArr['notification']['title'] = $row['heading'];
            $sendArr['notification']['body'] = $row['message'];
            //print_r($sendArr); exit;
            $url = 'https://fcm.googleapis.com/fcm/send';
            $api_key = 'AAAANjIIu60:APA91bHdkI8DsdzV41dFPFbMprFkxi8QUj-UZJNyQrw1DRJHSbv70DwctxU7BQ98BGqcR8OrgAgrUWHsuMGwq_MydaZa3ONQl8E7Scnh90wHBe9irpykUtR8hDQb80rRSKkGk_KXKKQF';

            $fields = array(
                'to' => "/topics/AllUsers",
                'data' => $sendArr,
            );
            $headers = array(
                'Authorization: key=' . $api_key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendArr));
            $result = curl_exec($ch);
            //print_r($result);
            curl_close($ch);
            $this->insert([
                "status" => "processed",
                "updated_on" => date("Y-m-d H:i:s")
            ], $updateId);
            exit;
        } else {
            $oCustomersModel = new \models\CustomersModel();
            $custData = $oCustomersModel->find(["fields" => "customer_id", "whereClause" => "email = ?", "whereParams" => ["s", $customer]]);
            $oCustomersGcmModel = new \models\UsersGcmModel();
            $users = $oCustomersGcmModel->findAll(["fields" => "*", "whereClause" => "customer_id = ? ", "whereParams" => ["i", $custData['customer_id']]]);
        }
        
        $oPushAlertsQueueModel = new \models\PushAlertsQueueModel();
        for($u = 0; $u < COUNT($users); $u++) {
            $gcm_id = $users[$u]['gcm_id'];
            $oPushAlertsQueueModel->addPush([
                "gcm_token" => $gcm_id,
                "heading" => $heading,
                "message" => $message,
            ]);
        }
        $this->insert([
            "status" => "processed",
            "updated_on" => date("Y-m-d H:i:s")
        ], $updateId);
    }

}
