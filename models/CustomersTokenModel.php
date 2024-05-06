<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class CustomersTokenModel extends AppModel {

    protected $relation = 'customers_token';
    protected $pk = 'auto_id';

    public function createAppToken($userInfo) {
        $token = md5($userInfo["user_id"] . \helpers\Common::genRandomId());
        $this->insert([
            "user_id" => $userInfo['customer_id'],
            "user_type" => "customer",
            "user_token" => $token,
            "platform" => !empty($this->getAzharConfigsByKey("APP_PLATFORM")) ? $this->getAzharConfigsByKey("APP_PLATFORM") : "android",
            "device_id" => !empty($this->getAzharConfigsByKey("APP_PLATFORM")) ? $this->getAzharConfigsByKey("APP_PLATFORM") : "android",
            "added_on" => date("Y-m-d H:i:s"),
        ]);
        return $token;
    }
    
    public function validateToken($token) {
        $tokenData = $this->findByFieldString('user_token', $token);
        return $tokenData;
    }

}
