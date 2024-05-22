<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class UserController extends AppController {

    public function registerAction() {
        $data = array();
        if ($this->isPOST()) {
            $post = $data['posted'] = $this->obtainPost();
            $oUsersModel = new \models\UsersModel();
            $user_registered = $oUsersModel->register_user($post);
            if ($user_registered['status'] == 'success') {
                $user_info = $user_registered['data'];
                $this->setGuest(false);
                $session_id = md5($user_info["user_id"].\helpers\Common::genRandomId());
                $user_info['token'] = $session_id;
            
                $oStoresModel = new \models\StoresModel();
                $storeInfo = $oStoresModel->findByPK($user_info['store_id'], "store_name");
                $user_info["store_name"] = $storeInfo['store_name'];
                $this->setState('user', $user_info);

                $oSessionsModel = new \models\SessionsModel();
                $oSessionsModel->insert([
                    "user_id" => $user_info['user_id'],
                    "session_id" => $session_id,
                    "session_time" => date("Y-m-d H:i:s"),
                    "user_type" => "web",
                    "ip_address" => \helpers\Common::getIP(),
                ]);
                $data['success'] = "Your account has been created.";
                $this->redirect(['url' => SITE_URL."dashboard", 'data' => $data]);
            } else {
                $data['error'] = $user_registered['error'];
            }
        }
        $metaData['title'] = 'Register Now on ' . DOMAIN_BRAND_NAME;
        $metaData['keywords'] = 'regietr,' . DOMAIN_BRAND_NAME;
        $metaData['description'] = 'Register now on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('register', $data);
    }

    public function forgotPasswordAction() {
        $data = array();
        if ($this->isPOST()) {
            $post = $this->obtainPost();
            $oUsersModel = new \models\UsersModel();
            $post['type'] = "customer";
            $data = $oUsersModel->forgotPassword($post);
            if ($data['status'] == 'Y') {
                $data['success'] = "Please check your email for reset password";
            } else {
                $data['error'] = $data['message'];
            }
        }
        $metaData['title'] = 'Forgot Password on ' . DOMAIN_BRAND_NAME;
        $metaData['keywords'] = 'forgot,password,recovery,' . DOMAIN_BRAND_NAME;
        $metaData['description'] = 'Forgot Password on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('forgot_password', $data);
    }

    public function logoutAction() {
        $userData = $this->getUserState();
        $oSessionsModelo = new \models\SessionsModel();
        $oSessionsModelo->delete([
            "whereClause" => "user_id = ? AND user_type = ? ",
            "whereParams" => ["is", $userData['user_id'], "web"],
        ]);
        $this->setState('user', '');
        $this->redirect(['url' => SITE_URL, 'data' => []]);
    }

    public function resetPasswordAction() {
        $data = array();
        $data['get'] = $this->obtainGet();
        if ($this->isPOST()) {
            $post = $this->obtainPost();
            if ($post['type'] == 'customer') {
                $oCustomersModel = new \models\CustomersModel();
                $data = $oCustomersModel->resetPassword($post);
                $data['get']['user'] = $post['user'];
            } else {
                $oUsersModel = new \models\UsersModel();
                $data = $oUsersModel->resetPassword($post);
            }
        }
        if (empty($data['get']['user'])) {
            $this->redirect(['url' => SITE_URL, 'data' => $data]);
        } else {
            $data['get']['user1'] = \helpers\Common::decoded($data['get']['user']);
        }
        $metaData['title'] = 'Reset Password on ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('reset_password', $data);
    }

}
