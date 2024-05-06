<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class CustomersModel extends AppModel {

    protected $relation = 'customers';
    protected $pk = 'customer_id';

    public function register($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['first_name'])) {
            $retData["message"] = $oLangsModel->findByPK("1", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['last_name'])) {
            $retData["message"] = $oLangsModel->findByPK("2", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['email'])) {
            $retData["message"] = $oLangsModel->findByPK("3", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['password'])) {
            $retData["message"] = $oLangsModel->findByPK("4", "lang_en")["lang_en"];
            return $retData;
        }
        if (!empty($this->getUserByEmail($params['email']))) {
            $retData["message"] = $oLangsModel->findByPK("5", "lang_en")["lang_en"];
            return $retData;
        }
        $refCode = \helpers\Common::genRandomString(8, "MIX");
        while(!empty($this->findByFieldString("refferal_code", $refCode, "customer_id"))) {
            $refCode = \helpers\Common::genRandomString(8, "MIX");
        }
        $oAppConfigModel = new \models\AppConfigModel();
        $refferalCreditsDefault = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "refferal_credits", "active"]])['field_value'];
        $signup_credits = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits", "active"]])['field_value'];
        $signup_credits_expiry = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits_expiry", "active"]])['field_value'];
        $refferal_credits = 0;
        if(!empty($signup_credits_expiry)) {
            if(strtotime(date("Y-m-d H:i:s", strtotime($signup_credits_expiry))) > strtotime(date("Y-m-d H:i:s"))) {
                $refferal_credits = $signup_credits;
            }
        }
        $customer_id = $this->insert([
            "first_name" => $params['first_name'],
            "last_name" => $params['last_name'],
            "email" => $params['email'],
            "pwd" => md5($params['password']),
            "refferal_code" => $refCode,
            //"refferal_credits" => $refferalCreditsDefault ?: 0,
            "credits" => $refferal_credits,
            "added_on" => date("Y-m-d H:i:s"),
            "ip_address" => \helpers\Common::getIP()
        ]);
        $searchArr = ["fields" => "customer_id, first_name, last_name, email, image, phone, address, notification, credits, refferal_code as referral_code", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $params['email'], "active"]];
        $user_info = $this->find($searchArr);
        $send_signup_email = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "send_signup_email", "active"]])['field_value'];
        if($send_signup_email == 'Y') {
            $mailParams = [
                "emailAddress" => $params['email'],
                "subject" => "Welcome to ".DOMAIN_NAME,
                "message" => "<b>Hello ".$params['first_name'].",</b><br/><br/>"
                . "You have successfully registered with  ".DOMAIN_NAME.", enjoy your membership and start calling  ".DOMAIN_NAME." on one of kind deals near you.<br/><br/>"
                . "<b>Thank you</b><br/>"
                . "The ".DOMAIN_NAME." Team",
                "cc" => "",
            ];
            \helpers\Common::sendMail($mailParams);
        }
        if(!empty($params['referral_code'])) {
            $this->addRefferal(['referral_code' => $params['referral_code']]);
        }
        $oCustomersTokenModel = new \models\CustomersTokenModel();
        $token = $oCustomersTokenModel->createAppToken($user_info);
        $user_info['token'] = $token;
        $retData["data"] = $user_info;
        $retData["code"] = "11";
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("6", "lang_en")["lang_en"];
        return $retData;
    }

    public function getUserByEmail($email) {
        return $this->find(["fields" => "*", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $email, "active"]]);
    }
    
    public function resetPassword($params) {
        $user = \helpers\Common::decoded($params['user']);
        $this->update(["fields" => "pwd = ?", "whereClause" => "email = ? AND status = ?", "whereParams" => ["sss", md5($params['password']), $user, "active"]]);
        return ["success" => "Password successfully updated"];
    }

    public function login($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['email'])) {
            $retData["message"] = $oLangsModel->findByPK("3", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['password'])) {
            $retData["message"] = $oLangsModel->findByPK("4", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($this->getUserByEmail($params['email']))) {
            $retData["message"] = $oLangsModel->findByPK("7", "lang_en")["lang_en"];
            return $retData;
        }
        $searchArr = ["fields" => "customer_id, first_name, last_name, email, image, phone, address, notification, credits, refferal_code as referral_code", "whereClause" => "email = ? AND pwd = ? AND status = ?", "whereParams" => ["sss", $params['email'], md5($params['password']), "active"]];
        $user_info = $this->find($searchArr);
        if (!empty($user_info)) {
            $oCustomersTokenModel = new \models\CustomersTokenModel();
            $token = $oCustomersTokenModel->createAppToken($user_info);
            $user_info['token'] = $token;
            $user_info['notification'] = "Y";
            
            $oDiscountCoupensModel = new \models\DiscountCoupensModel();
            $coupens = $oDiscountCoupensModel->getUserCoupens($user_info['email']);
            $user_info['coupens'] = $coupens;
            
            $oAppConfigModel = new \models\AppConfigModel();
            $user_info['support_email'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]])['field_value'];
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["data"] = $user_info;
            if(!empty($params['gcm_id'])) {
                $oUsersGcmModel = new \models\UsersGcmModel();
                $params['customer_id'] = $user_info['customer_id'];
                $oUsersGcmModel->addGcm($params);
            }
            $retData["message"] = $oLangsModel->findByPK("9", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("8", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function forgotPassword($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['email'])) {
            $retData["message"] = $oLangsModel->findByPK("3", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($this->getUserByEmail($params['email']))) {
            $retData["message"] = $oLangsModel->findByPK("7", "lang_en")["lang_en"];
            return $retData;
        }
        $searchArr = ["fields" => "customer_id, first_name, last_name, email, image, phone, address", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $params['email'], "active"]];
        $user_info = $this->find($searchArr);
        if (!empty($user_info)) {
            $param['emailAddress'] = $params['email'];
            $param['cc'] = '';
            $param['bcc'] = '';
            $param['subject'] = "Forgot Password On ".DOMAIN_NAME."";
            $param['message'] = "Dear ".$user_info['first_name'].",<br /><br />"
                . "Please follow this link to reset your password<br />"
                . "".SITE_URL."user/resetPassword/?user=".\helpers\Common::encoded($user_info['email'])."&type=customer<br /><br />"
                . "Thank you<br />"
                . "The ".DOMAIN_NAME." Team";
            $param['fromName'] = "DIBBS";
            \helpers\Common::sendMail($param);
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["data"]['link'] = SITE_URL."user/resetPassword/?user=".\helpers\Common::encoded($user_info['email'])."&type=customer";
            $retData["message"] = $oLangsModel->findByPK("27", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("8", "lang_en")["lang_en"];
        }
        return $retData;
    }

    public function logout($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $oCustomersTokenModel = new \models\CustomersTokenModel();
        $delArr = ["whereClause" => " user_id = ? AND user_type = ? ", "whereParams" => ["is", $this->getAzharConfigsByKey("USER_ID"), "customer"]];
        $loggedOut = $oCustomersTokenModel->delete($delArr);
        $oUsersGcmModel = new \models\UsersGcmModel();
        $delArr1 = ["whereClause" => " customer_id = ? ", "whereParams" => ["i", $this->getAzharConfigsByKey("USER_ID")]];
        $oUsersGcmModel->delete($delArr1);
        if (!empty($loggedOut)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("11", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("10", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function updateSupport($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['id'])) {
            $retData["message"] = $oLangsModel->findByPK("47", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['status'])) {
            $retData["message"] = $oLangsModel->findByPK("48", "lang_en")["lang_en"];
            return $retData;
        }
        $oCustomerSupportModel = new \models\CustomerSupportModel();
        $insertId = $oCustomerSupportModel->insert([
            "status" => $params['status'],
            "updated_on" => date("Y-m-d H:i:s"),
        ], $params['id']);
        if (!empty($insertId)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("49", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("14", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function support($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['email'])) {
            $retData["message"] = $oLangsModel->findByPK("40", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['message'])) {
            $retData["message"] = $oLangsModel->findByPK("41", "lang_en")["lang_en"];
            return $retData;
        }
        $oCustomerSupportModel = new \models\CustomerSupportModel();
        $insertId = $oCustomerSupportModel->insert([
            "customer_id" => $this->getAzharConfigsByKey("USER_ID"),
            "message" => $params['message'],
            "email" => $params['email'],
            "order_id" => $params['order_id'],
            "added_on" => date("Y-m-d H:i:s"),
        ]);
        $oCustomersModel = new \models\CustomersModel();
        $custInfo = $oCustomersModel->findByPK($this->getAzharConfigsByKey("USER_ID"));
        $oAppConfigModel = new \models\AppConfigModel();
        $support_email = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]])['field_value'];
        if(!empty($support_email)) {
            $mailParams = [
                "emailAddress" => $support_email,
                "subject" => "New Support Query at ".DOMAIN_NAME,
                "message" => "<b>Dear Admin,</b><br/><br/>"
                . "This message was received from a customer on ".DOMAIN_NAME.".<br/>
                Details are as follows:<br/><br/>"
                . "<b>Email</b>: ".$params['email']."<br/>"
                . "<b>Message</b>: ".nl2br($params['message'])."<br/>"
                . "<b>Order Id</b>: ".($params['order_id'] ?: '')."<br/><br/>"
                . "Please respond to this query at your earliest.<br/><br/>"
                . "<b>Regards</b><br/>"
                . "The ".DOMAIN_NAME." Team",
                "cc" => "",
                "bcc" => $params['email'].",",
                "replyTo" => $params['email'],
            ];
            \helpers\Common::sendMail($mailParams);
        }
        $mailParams = [
            "emailAddress" => $params['email'],
            "subject" => "Thanks for your feedback on ".DOMAIN_NAME,
            "message" => "<b>Hello ".$custInfo['first_name'].",</b><br/><br/>
            We’ve received your message. Please allow up to 48 hours to receive a reply from one of our team representatives.<br/><br/>
            <b>Thank you,</b><br/>"
            . "The ".DOMAIN_NAME." Team",
            "cc" => "",
            "bcc" => "",
        ];
        \helpers\Common::sendMail($mailParams);
        if (!empty($insertId)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("42", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("14", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function addRefferal($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['referral_code'])) {
            $retData["message"] = $oLangsModel->findByPK("43", "lang_en")["lang_en"];
            return $retData;
        }
        $thisUser = $this->findByPK($this->getAzharConfigsByKey("USER_ID"), "partner_id");
        if (!empty($thisUser['partner_id'])) {
            $retData["message"] = $oLangsModel->findByPK("45", "lang_en")["lang_en"];
            return $retData;
        }
        $refferUser = $this->findByFieldString("refferal_code", $params['referral_code']);
        if (empty($refferUser)) {
            $retData["message"] = $oLangsModel->findByPK("44", "lang_en")["lang_en"];
            return $retData;
        }
        $upCustArr = [
            "partner_id" => $params['referral_code']
        ];
        $insertId = $this->insert($upCustArr, $this->getAzharConfigsByKey("USER_ID"));
        $oAppConfigModel = new \models\AppConfigModel();
        $refferal_credits_default = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "refferal_credits", "active"]])['field_value'];
        $refferal_credits = $refferUser['refferal_credits'] > 0 ? $refferUser['refferal_credits'] : $refferal_credits_default;
        if($refferal_credits > 0) {
            $this->insert(["credits" => ($refferUser['credits'] + $refferal_credits)], $refferUser['customer_id']);
        }
        
        if (!empty($insertId)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("46", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("14", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function updateProfileSetting($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $upArr = ["fields" => "notification = ?", "whereClause" => " customer_id = ? ", "whereParams" => ["si", $params['notification'], $this->getAzharConfigsByKey("USER_ID")]];
        $loggedOut = $this->update($upArr);
        if (!empty($loggedOut) || 1) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("34", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("10", "lang_en")["lang_en"];
        }
        return $retData;
    }
    
    public function updateCredits($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $upArr = ["fields" => "refferal_credits = ?", "whereClause" => " customer_id = ? ", "whereParams" => ["si", $params['credits'], $params['auto_id']]];
        $loggedOut = $this->update($upArr);
        if (!empty($loggedOut)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("34", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("10", "lang_en")["lang_en"];
        }
        return $retData;
    }

    public function getUsersList($arr = []) {
        $searchFilters = $this->getState('customerSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status <> ?  ",
            "whereParams" => ["s", 'deleted']
        ];
        if (!empty($searchFilters['keyword'])) {
            $searchArr["whereClause"] .= " AND (first_name LIKE '%" . $searchFilters['keyword'] . "%' OR last_name LIKE '%" . $searchFilters['keyword'] . "%' OR email LIKE '%" . $searchFilters['keyword'] . "%') ";
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $userData = $this->findAll($searchArr);

        /* $oAdminuserModel = new \models\AdminuserModel();
          for ($d = 0; $d < COUNT($industryData); $d++) {
          $userId = $industryData[$d]['created_by'];
          $industryData[$d]['userName'] = $oAdminuserModel->getAdminUserById($userId);
          } */

        return ['users' => $userData, 'totalRecords' => $totalRecords];
    }

}
