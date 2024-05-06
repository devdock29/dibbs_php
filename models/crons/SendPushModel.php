<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models\crons;

class SendPushModel extends \models\crons\CronModel {

    protected $relation = 'push_alerts_queue';
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
        $gcms = $row['gcm_token'];
        if (empty($gcms)) {
            $oUserGcmModel = new \models\UsersGcmModel();
            $gcms = $oUserGcmModel->findByFieldString("user_id", $row['user_id'], 'user_gcm')['user_gcm'];
        }
        //print_r($gcms);
        $gcmIds = [$gcms];

        $sendArr = array();
        /*$sendArr['notificationId'] = $row['auto_id'];
        $sendArr['notificationTitle'] = $row['heading'];
        $sendArr['notificationMessage'] = $row['message'];
        $sendArr['type'] = $row['type'];
        $sendArr['title'] = $row['heading'];
        $sendArr['message'] = $row['message'];*/
		
		
		$sendArr['to'] = $gcms;
        $sendArr['notification']['title'] = $row['heading'];
        $sendArr['notification']['body'] = $row['message'];
        //print_r($sendArr); exit;

        if (COUNT($gcmIds) > 0 && !empty($sendArr) && !empty($row['message'])) {
            $this->update(array('fields' => ' status = ? ', 'whereClause' => ' auto_id = ? ', 'whereParams' => ['si', 'Processing', $updateId]));
            //$url = 'https://android.googleapis.com/gcm/send';
            $url = 'https://fcm.googleapis.com/fcm/send';
            $api_key = 'AAAANjIIu60:APA91bHdkI8DsdzV41dFPFbMprFkxi8QUj-UZJNyQrw1DRJHSbv70DwctxU7BQ98BGqcR8OrgAgrUWHsuMGwq_MydaZa3ONQl8E7Scnh90wHBe9irpykUtR8hDQb80rRSKkGk_KXKKQF';

            $fields = array(
                //'registration_ids' => $gcmIds,
                'to' => $gcmIds,
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
            //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendArr));
            $result = curl_exec($ch);
            //print_r($result);
            curl_close($ch);

            $array = json_decode($result, true);
            $result = $array['results'];
            if ($result && ($result['failure'] > 0 || $result['canonical_ids'] > 0)) {
                $errorArr = ["InvalidRegistration", "NotRegistered"];
                /*for ($er = 0; $er < count($gcmIds); $er++) {
                    if (isset($result[$er]['error']) && in_array($result[$er]['error'], $errorArr)) {
                        $oUserGcmModel->delete(array('whereClause' => 'gcm_id = ? ', 'whereParams' => ['s', $gcmIds[$er]]));
                    }
                    if (isset($result[$er]['registration_id'])) {
                        $id = $result[$er]['registration_id'];
                        $isNewExist = $oUserGcmModel->info(array('fields' => 'gcm_id', 'whereClause' => ' gcm_id = ? ', 'whereParams' => ['s', $id]));
                        if ($isNewExist['gcm_android']) {
                            $oUserGcmModel->delete(array('whereClause' => 'gcm_id = ? ', 'whereParams' => ['s', $gcmIds[$er]]));
                        } else {
                            $oUserGcmModel->update(array('fields' => 'gcm_id = ? ', 'whereClause' => ' gcm_id = ? ', 'whereParams' => ['ss', $id, $gcmIds[$er]]));
                        }
                    }
                }*/
            }

            $success = $array['success'];
            $sendStatus = $success > 0 ? "sent" : "failed";
            $this->update(array('fields' => 'push_data = ?, response_status = ?, push_response = ?, status = ?, push_sent_on = ? ', 'whereClause' => ' auto_id = ? ', 'whereParams' => ['sssssi', "Sent Params : \n" . print_r($fields, true).print_r($headers, true), "Response : \n" . print_r($array, true), $sendStatus, 'Processed', date("Y-m-d H:i:s"), $updateId]));
            $this->update(array('fields' => ' status = ?, push_sent_on = ? ', 'whereClause' => ' auto_id = ? ', 'whereParams' => ['ssi', 'Processed', date("Y-m-d H:i:s"), $updateId]));
        } else {
            $this->update(array('fields' => 'response_status = ?, status = ?, push_sent_on = ? ', 'whereClause' => ' auto_id = ? ', 'whereParams' => ['sssi', "Failed - No GCM", 'Processsed', date("Y-m-d H:i:s"), $updateId]));
        }
    }

}
