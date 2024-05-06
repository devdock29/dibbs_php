<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class DashboardController extends StateController {

    public function indexAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $data['paidOrders'] = $data['pendingOrders'] = $data['totProducts'] = $data['totFaqs'] = 0;
        $oProductsModel = new \models\ProductsModel();
        $data['totProducts'] = $oProductsModel->count(["fields" => "COUNT(product_id)", "whereClause" => " status = ? ", "whereParams" => ["s", "active"]]);
        
        $oFaqsModel = new \models\FaqsModel();
        $data['totFaqs'] = $oFaqsModel->count(["fields" => "COUNT(auto_id)", "whereClause" => " status = ? ", "whereParams" => ["s", "active"]]);
        
        $oDiscountCoupensModel = new \models\DiscountCoupensModel();
        $data['totCoupons'] = $oDiscountCoupensModel->count(["fields" => "COUNT(auto_id)", "whereClause" => " status = ? ", "whereParams" => ["s", "active"]]);
        
        $oCustomersModel = new \models\CustomersModel();
        $data['totCustomers'] = $oCustomersModel->count(["fields" => "COUNT(customer_id)", "whereClause" => " status = ? ", "whereParams" => ["s", "active"]]);
        
        $oOrdersModel = new \models\OrdersModel();
        $orders = $oOrdersModel->findAll(["fields" => "payment_status, COUNT(order_id) as tot", "whereClause" => " status <> ? GROUP BY payment_status ", "whereParams" => ["s", "deleted"]]);
        for($o = 0; $o < COUNT($orders); $o++) {
            if($orders[$o]['payment_status'] == 'paid') {
                $data['paidOrders'] = $orders[$o]['tot'];
            }
            if($orders[$o]['payment_status'] == 'pending') {
                $data['pendingOrders'] = $orders[$o]['tot'];
            }
        }
        $metaData['title'] = 'Admin Dashboard -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);

        $this->renderT('index', $data);
    }
    
    public function profileAction() {
        $data = array();
        $data['userState'] = $this->getState('adminUser');
        $oUsersModel = new \models\UsersModel();
        $oAppConfigModel = new \models\AppConfigModel();
        if($this->isPOST()) {
            $post = $this->obtainPost();
            $upProfile = [
                "user_name" => $post['user_name'],
            ];
            if(!empty($post['password'])) {
                $upProfile['pwd'] = md5($post['password']);
            }
            if (!empty($_FILES['image_profile']['name'][$v])) {
                $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
                $dir = "uploads/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }
                if (!empty($_FILES['image_profile']['tmp_name'])) {
                    $ext = pathinfo($_FILES['image_profile']['name'], PATHINFO_EXTENSION);
                    if (in_array($ext, $allowed)) {
                        $randId = \helpers\Common::genRandomId();
                        $bucket = \helpers\Common::genRandomString(2, 'INT');
                        if (!file_exists($dir . $bucket . "/")) {
                            mkdir($dir . $bucket . "/", 0777);
                        }
                        $imgFileName = time() . '_' . $randId;
                        $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
                        if (move_uploaded_file($_FILES['image_profile']['tmp_name'], $fullPath)) {
                            //$image = $fullPath;
                            $upProfile['image'] = $fullPath;
                        }
                    }
                }
            }
            $oUsersModel->insert($upProfile,$post['user_id']);
            $adminUser = $this->getState('adminUser');
            $user_info = $oUsersModel->findByPK($post['user_id']);
            $user_info['token'] = $adminUser['token'];
            $this->setState('adminUser', $adminUser);
            if(!empty($post['coupen_code'])) {
                $coupenCode = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_code", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "coupen_code",
                    "field_value" => $post['coupen_code'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $coupenCode['auto_id']);
            }
            if(!empty($post['coupen_amount'])) {
                $coupenCode = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_amount", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "coupen_amount",
                    "field_value" => $post['coupen_amount'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $coupenCode['auto_id']);
            }
            if(!empty($post['support_email'])) {
                $coupenCode = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "support_email",
                    "field_value" => $post['support_email'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $coupenCode['auto_id']);
            }
            if(!empty($post['refferal_credits'])) {
                $refferalCredits = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "refferal_credits", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "refferal_credits",
                    "field_value" => $post['refferal_credits'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $refferalCredits['auto_id']);
            }
            if(!empty($post['product_admin_share'])) {
                $adminShare = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "product_admin_share", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "product_admin_share",
                    "field_value" => $post['product_admin_share'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $adminShare['auto_id']);
            }
            if(!empty($post['send_signup_email'])) {
                $signUpEmail = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "send_signup_email", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "send_signup_email",
                    "field_value" => $post['send_signup_email'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $signUpEmail['auto_id']);
            }
            if(!empty($post['signup_credits'])) {
                $adminShare = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "signup_credits",
                    "field_value" => $post['signup_credits'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $adminShare['auto_id']);
            }
            if(!empty($post['signup_credits_expiry'])) {
                $signUpEmail = $oAppConfigModel->find(["fields" => "auto_id, field_name", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits_expiry", "active"]]);
                $oAppConfigModel->insert([
                    "field_name" => "signup_credits_expiry",
                    "field_value" => $post['signup_credits_expiry'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $data['userState']['user_id'],
                    "updated_on" => date("Y-m-d H:i:s"),
                    "updated_by" => $data['userState']['user_id'],
                ], $signUpEmail['auto_id']);
            }
            $data['success'] = "Profile Successfully Updated";
            $this->redirect(['url' => ADMIN_URL."dashboard", 'data' => $data]);
        }
        $data['profile'] = $oUsersModel->find(["fields" => "*", "whereClause" => " user_id = ? ", "whereParams" => ["i", $data['userState']['user_id']]]);
        $data['profile']['coupen_code'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_code", "active"]])['field_value'];
        $data['profile']['coupen_amount'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "coupen_amount", "active"]])['field_value'];
        $data['profile']['support_email'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]])['field_value'];
        $data['profile']['refferal_credits'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "refferal_credits", "active"]])['field_value'];
        $data['profile']['product_admin_share'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "product_admin_share", "active"]])['field_value'];
        $data['profile']['send_signup_email'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "send_signup_email", "active"]])['field_value'];
        $data['profile']['signup_credits'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits", "active"]])['field_value'];
        $data['profile']['signup_credits_expiry'] = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "signup_credits_expiry", "active"]])['field_value'];
        
        $metaData['title'] = 'Admin Profile -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data); exit;
        $this->renderT('profile', $data);
    }

}
