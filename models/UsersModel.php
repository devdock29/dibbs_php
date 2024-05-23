<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class UsersModel extends AppModel {
    protected $relation = 'users';
    protected $pk = 'user_id';
    
    public function register_user($params) {
        $retData = ['status' => "error"];
        if(empty($params['user_name'])) {
            $retData["error"] = "Please enter full Name";
            return $retData;
        }
        if(empty($params['email'])) {
            $retData["error"] = "Please enter email";
            return $retData;
        }
        if(empty($params['password'])) {
            $retData["error"] = "Please enter password";
            return $retData;
        }
        if (empty($params['cpassword'])) {
            $retData["message"] = "Confirm Password is required";
            return $retData;
        }
        if ($params['cpassword'] != $params['password']) {
            $retData["message"] = "Password and confirm password must be same";
            return $retData;
        }
        if(empty($params['store_name'])) {
            $retData["error"] = "Please enter store Name";
            return $retData;
        }
        if(!empty($this->getUserByEmail($params['email']))) {
            $retData["error"] = "Email already in use, Please try another one";
            return $retData;
        }
        $user_id = $this->insert([
            "user_name" => $params['user_name'],
            "email" => $params['email'],
            "pwd" => md5($params['password']),
            "store_id" => 0,
            "added_on" => date("Y-m-d H:i:s"),
            "ip_address" => \helpers\Common::getIP()
        ]);
        $oStoresModel = new \models\StoresModel();
        $store_id = $oStoresModel->insert([
            "user_id" => $user_id,
            "store_name" => $params['store_name'],
            "added_on" => date("Y-m-d H:i:s"),
        ]);
        $this->insert(["store_id" => $store_id], $user_id);
        $retData["status"] = "success";
        $retData["data"] = ['user_id' => $user_id, 'store_id' => $store_id];
        $retData["success"] = "Store has been registered successfully registered, Login now";
        return $retData;
    }
    
    public function getUserByEmail($email) {
        return $this->find(["fields" => "*", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $email, "active"]]);
    }
    
    public function login($params) {
        $retData = ['status' => "error"];
        if(empty($params['email'])) {
            $retData["error"] = "Please enter email";
            return $retData;
        }
        if(empty($params['password'])) {
            $retData["error"] = "Please enter password";
            return $retData;
        }
        $isAdmin = $params["admin"] == "Y" ? "Y" : "N";
        $searchArr = ["fields" => "*", "whereClause" => "email = ? AND pwd = ? AND status = ?", "whereParams" => ["sss", $params['email'], md5($params['password']), "active"]];
        // $searchArr = ["fields" => "*", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $params['email'], "active"]];
        if($isAdmin == "Y") {
            $searchArr["whereClause"] .= " AND user_role = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = "admin";
        } else {
            $searchArr["whereClause"] .= " AND user_role = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = "owner";
        }
        $user_info = $this->find($searchArr);
        if(!empty($user_info)) {
            $this->setGuest(false);
            $session_id = md5($user_info["user_id"].\helpers\Common::genRandomId());
            $user_info['token'] = $session_id;
            if($isAdmin == "Y") {
                $this->setState('adminUser', $user_info);
            } else {
                $oStoresModel = new \models\StoresModel();
                $storeInfo = $oStoresModel->findByPK($user_info['store_id'], "store_name");
                $user_info["store_name"] = $storeInfo['store_name'];
                $this->setState('user', $user_info);
            }
            $oSessionsModel = new \models\SessionsModel();
            $oSessionsModel->insert([
                "user_id" => $user_info['user_id'],
                "session_id" => $session_id,
                "session_time" => date("Y-m-d H:i:s"),
                "user_type" => $isAdmin == "Y" ? "admin" : "web",
                "ip_address" => \helpers\Common::getIP(),
            ]);
            $retData["status"] = "success";
            $retData["success"] = "Welcome Back.!";
        } else {
            $retData["error"] = "Invalid Email / Password";
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
        $searchArr = ["fields" => "user_id, email, user_name", "whereClause" => "email = ? AND status = ?", "whereParams" => ["ss", $params['email'], "active"]];
        $user_info = $this->find($searchArr);
        if (!empty($user_info)) {
            $param['emailAddress'] = $params['email'];
            $param['cc'] = '';
            $param['bcc'] = '';
            $param['subject'] = "Forgot Password On DIBBS";
            $param['message'] = "Dear ".$user_info['user_name'].",<br /><br />Please follow this link to reset your password on dibbs<br />".SITE_URL."user/resetPassword/?user=".\helpers\Common::encoded($user_info['email'])."&type=store<br /><br />Regards,<br />The Dibbs Team";
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
    
    public function resetPassword($params) {
        $user = \helpers\Common::decoded($params['user']);
        $this->update(["fields" => "pwd = ?", "whereClause" => "email = ? AND status = ?", "whereParams" => ["sss", md5($params['password']), $user, "active"]]);
        return ["success" => "Password successfully updated"];
    }
    
    public function getUsersList($arr = []) {
        $searchFilters = $this->getState('userSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " status = ?  ",
            "whereParams" => ["s", 'active']
        ];
        if (!empty($searchFilters['search'])) {
            $searchArr["whereClause"] .= " AND (first_name LIKE '%" . $searchFilters['search'] . "%' OR last_name LIKE '%" . $searchFilters['search'] . "%' OR email LIKE '%" . $searchFilters['search'] . "%') ";
        }
        /*if (!empty($searchFilters['type'])) {
            $searchArr["whereClause"] .= " AND type = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = $searchFilters['type'];
        }*/
        $totalRecords = $this->count(array("fields" => " COUNT(".$this->pk.")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $userData = $this->findAll($searchArr);

        /*$oAdminuserModel = new \models\AdminuserModel();
        for ($d = 0; $d < COUNT($industryData); $d++) {
            $userId = $industryData[$d]['created_by'];
            $industryData[$d]['userName'] = $oAdminuserModel->getAdminUserById($userId);
        }*/

        return ['users' => $userData, 'totalRecords' => $totalRecords];
    }
}
