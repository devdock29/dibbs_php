<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class DashboardController extends StateController {

    public function indexAction() {
        $data = array();
        $data['paidOrders'] = $data['pendingOrders'] = $data['totProducts'] = 0;
        $data['userState'] = $this->getState('user');
        $store_id = $data['userState']['store_id'];
        $oProductsModel = new \models\ProductsModel();
        $data['totProducts'] = $oProductsModel->count(["fields" => "COUNT(product_id)", "whereClause" => " store_id = ? AND status = ? ", "whereParams" => ["is", $store_id, "active"]]);
        
        $oOrdersModel = new \models\OrdersModel();
        $orders = $oOrdersModel->findAll(["fields" => "payment_status, COUNT(order_id) as tot", "whereClause" => " store_id = ? AND status <> ? GROUP BY payment_status ", "whereParams" => ["is", $store_id, "deleted"]]);
        for($o = 0; $o < COUNT($orders); $o++) {
            if($orders[$o]['payment_status'] == 'paid') {
                $data['paidOrders'] = $orders[$o]['tot'];
            }
            if($orders[$o]['payment_status'] == 'pending') {
                $data['pendingOrders'] = $orders[$o]['tot'];
            }
        }
        
        $metaData['title'] = $data['userState']['store_name']. ' Dashboard | ' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data); exit;
        $this->renderT('index', $data);
    }
    
    public function profileAction() {
        $data = array();
        $data['userState'] = $this->getState('user');
        $oUsersModel = new \models\UsersModel();
        $oStoresModel = new \models\StoresModel();
        $oStoreTimingsModel = new \models\StoreTimingsModel();
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
            $oUsersModel->insert($upProfile, $post['user_id']);
            $adminUser = $this->getState('user');
            $user_info = $oUsersModel->findByPK($post['user_id']);
            $user_info['token'] = $adminUser['token'];
            $oStoresModel = new \models\StoresModel();
            $storeInfo = $oStoresModel->findByPK($user_info['store_id'], "store_name");
            $user_info["store_name"] = $storeInfo['store_name'];
            $this->setState('user', $user_info);
            $upSProfile = [
                "store_name" => $post['store_name'],
                "phone" => $post['phone'],
                "website" => $post['website'],
                "location" => $post['latlng'],
                "redeem_code" => $post['redeem_code'],
                "coupen_code" => $post['coupen_code'],
                "coupen_amount" => $post['coupen_amount'],
                "address" => $post['user_address'],
                "start_time" => date("H:i:s", strtotime($post['start_time'])),
                "end_time" => date("H:i:s", strtotime($post['end_time'])),
            ];
            if (!empty($_FILES['store_logo']['name'])) {
                $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
                $dir = "uploads/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }
                if (!empty($_FILES['store_logo']['tmp_name'])) {
                    $ext = pathinfo($_FILES['store_logo']['name'], PATHINFO_EXTENSION);
                    if (in_array($ext, $allowed)) {
                        $randId = \helpers\Common::genRandomId();
                        $bucket = \helpers\Common::genRandomString(2, 'INT');
                        if (!file_exists($dir . $bucket . "/")) {
                            mkdir($dir . $bucket . "/", 0777);
                        }
                        $imgFileName = time() . '_' . $randId;
                        $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
                        if (move_uploaded_file($_FILES['store_logo']['tmp_name'], $fullPath)) {
                            //$image = $fullPath;
                            $upSProfile['image'] = $fullPath;
                        }
                    }
                }
            }
            $oStoresModel->insert($upSProfile, $post['store_id']);
            $upStoreTimings = [
                "sat_close" => $post['sat_close']??"N",
                "sat_start" => $post['sat_start'] ? date("H:i:s", strtotime($post['sat_start'])) : NULL,
                "sat_end" => $post['sat_end'] ? date("H:i:s", strtotime($post['sat_end'])) : NULL,
                "sun_close" => $post['sun_close']??"N",
                "sun_start" => $post['sun_start'] ? date("H:i:s", strtotime($post['sun_start'])) : NULL,
                "sun_end" => $post['sun_end'] ? date("H:i:s", strtotime($post['sun_end'])) : NULL,
                "mon_close" => $post['mon_close']??"N",
                "mon_start" => $post['mon_start'] ? date("H:i:s", strtotime($post['mon_start'])) : NULL,
                "mon_end" => $post['mon_end'] ? date("H:i:s", strtotime($post['mon_end'])) : NULL,
                "tue_close" => $post['tue_close']??"N",
                "tue_start" => $post['tue_start'] ? date("H:i:s", strtotime($post['tue_start'])) : NULL,
                "tue_end" => $post['tue_end'] ? date("H:i:s", strtotime($post['tue_end'])) : NULL,
                "wed_close" => $post['wed_close']??"N",
                "wed_start" => $post['wed_start'] ? date("H:i:s", strtotime($post['wed_start'])) : NULL,
                "wed_end" => $post['wed_end'] ? date("H:i:s", strtotime($post['wed_end'])) : NULL,
                "thur_close" => $post['thur_close']??"N",
                "thur_start" => $post['thur_start'] ? date("H:i:s", strtotime($post['thur_start'])) : NULL,
                "thur_end" => $post['thur_end'] ? date("H:i:s", strtotime($post['thur_end'])) : NULL,
                "fri_close" => $post['fri_close']??"N",
                "fri_start" => $post['fri_start'] ? date("H:i:s", strtotime($post['fri_start'])) : NULL,
                "fri_end" => $post['fri_end'] ? date("H:i:s", strtotime($post['fri_end'])) : NULL,
            ];
            $oStoreTimingsModel->insert($upStoreTimings, $post['time_id']);
            $data['success'] = "Profile Successfully Updated";
            $this->redirect(['url' => SITE_URL."dashboard", 'data' => $data]);
        }
        $data['profile'] = $oUsersModel->find(["fields" => "*", "whereClause" => " user_id = ? ", "whereParams" => ["i", $data['userState']['user_id']]]);
        $data['profile']['store'] = $oStoresModel->findByPK($data['profile']['store_id']);
        $data['profile']['store_timings'] = $oStoreTimingsModel->findByFieldInt("store_id", $data['profile']['store_id']);
        if(empty($data['profile']['store_timings'])) {
            $oStoreTimingsModel->insert([
                "store_id" => $data['profile']['store_id'],
                "added_on" => date("Y-m-d H:i:s"),
            ]);
            $data['profile']['store_timings'] = $oStoreTimingsModel->findByFieldInt("store_id", $data['profile']['store_id']);
        }
        
        $metaData['title'] = 'User Profile -' . DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        //print_r($data); exit;
        $this->renderT('profile', $data);
    }

}
