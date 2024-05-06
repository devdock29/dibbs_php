<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class OrdersModel extends AppModel {

    protected $relation = 'orders';
    protected $pk = 'order_id';

    public function getOrdersList($arr = []) {
        $searchFilters = $this->getState('orderSearch');
        $searchArr = [
            "fields" => "*",
            "whereClause" => " 1 ",
            "whereParams" => [""]
        ];
        if (!empty($arr['store_id'])) {
            $searchArr["whereClause"] .= " AND store_id = ? ";
            $searchArr["whereParams"][0] .= "i";
            $searchArr["whereParams"][] = $arr['store_id'];
        }
        if (!empty($searchFilters['customer_id'])) {
            $searchArr["whereClause"] .= " AND customer_id = ? ";
            $searchArr["whereParams"][0] .= "i";
            $searchArr["whereParams"][] = $searchFilters['customer_id'];
        }
        if (!empty($searchFilters['status'])) {
            $searchArr["whereClause"] .= " AND status = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = $searchFilters['status'];
        }
        $totalRecords = $this->count(array("fields" => " COUNT(" . $this->pk . ")", "whereClause" => $searchArr["whereClause"], "whereParams" => $searchArr["whereParams"]));

        $searchArr["whereClause"] .= " ORDER BY added_on DESC LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= 'ii';
        $searchArr["whereParams"][] = $arr['offset'];
        $searchArr["whereParams"][] = $arr['perpage'];
        $orderData = $this->findAll($searchArr);

        $oOrderItemsModel = new \models\OrderItemsModel();
        $oStoresModel = new \models\StoresModel();
        $oCustomersModel = new \models\CustomersModel();
        for ($d = 0; $d < COUNT($orderData); $d++) {
            $orderItems = $oOrderItemsModel->findAll(["fields" => "product_id, variation_id, quantity, unit_price, total_price", "whereClause" => "order_id = ? AND status = ? ", "whereParams" => ["is", $orderData[$d]['order_id'], "active"]]);
            $oProductsModel = new \models\ProductsModel();
            $totorderItems = COUNT($orderItems);
            for ($i = 0; $i < $totorderItems; $i++) {
                $orderItems[$i]['prodData'] = $oProductsModel->getProductDetails($orderItems[$i]['product_id']);
            }
            $orderData[$d]['items'] = $orderItems;
            $orderData[$d]['tot_items'] = $totorderItems;
            $storeInfo = $oStoresModel->getStoreProfile($orderData[$d]['store_id']);
            $orderData[$d]['store_info'] = $storeInfo;
            $custInfo = $oCustomersModel->findByPK($orderData[$d]['customer_id']);
            $orderData[$d]['cust_info'] = $custInfo;
        }

        return ['orders' => $orderData, 'totalRecords' => $totalRecords];
    }

    public function getOrderDetails($order_id) {
        $order_data = $this->findByPK($order_id);
        $oOrderItemsModel = new \models\OrderItemsModel();
        $orderItems = $oOrderItemsModel->findAll(["fields" => "product_id, variation_id, quantity, unit_price, total_price", "whereClause" => "order_id = ? AND status = ? ", "whereParams" => ["is", $order_id, "active"]]);
        $oProductsModel = new \models\ProductsModel();
        for ($i = 0; $i < COUNT($orderItems); $i++) {
            $orderItems[$i]['prodData'] = $oProductsModel->getProductDetails($orderItems[$i]['product_id']);
        }
        $oProductVariationsModel = new \models\ProductVariationsModel();
        $order_data['variations'] = $oProductVariationsModel->findByPK($orderItems[0]['variation_id']);
        $order_data['items'] = $orderItems;
        $order_data['product'] = $orderItems[0];
        $oCustomersModel = new \models\CustomersModel();
        $customerData = $oCustomersModel->findByPK($order_data['customer_id'], "email, first_name, last_name, image, address");
        $order_data['custData'] = $customerData;
        $oOrderPaymentsModel = new \models\OrderPaymentsModel();
        $paymentData = $oOrderPaymentsModel->getOrderPayments($order_id);
        $order_data['paymentData'] = $paymentData;
        return $order_data;
    }

    public function createOrder($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['product_list'])) {
            $retData["message"] = $oLangsModel->findByPK("33", "lang_en")["lang_en"];
            return $retData;
        }
        /* if (empty($params['product_id'])) {
          $retData["message"] = $oLangsModel->findByPK("15", "lang_en")["lang_en"];
          return $retData;
          }
          if (empty($params['variation_id'])) {
          $retData["message"] = $oLangsModel->findByPK("20", "lang_en")["lang_en"];
          return $retData;
          }
          if (empty($params['quantity'])) {
          $retData["message"] = $oLangsModel->findByPK("21", "lang_en")["lang_en"];
          return $retData;
          }
          if (empty($params['unit_price'])) {
          $retData["message"] = $oLangsModel->findByPK("22", "lang_en")["lang_en"];
          return $retData;
          } */
        if (empty($params['total_price'])) {
            $retData["message"] = $oLangsModel->findByPK("23", "lang_en")["lang_en"];
            return $retData;
        }
        if (!isset($params['owner_amount']) || empty($params['owner_amount'])) {
            $params['owner_amount'] = 0;
            //$retData["message"] = $oLangsModel->findByPK("24", "lang_en")["lang_en"];
            //return $retData;
        } 
        $oCustomersModel = new \models\CustomersModel();
        $custCredits = $oCustomersModel->findByPK($params['customer_id'], "credits")['credits'];
        if(0) {
            if($params['owner_amount'] > 0 && $params['paymentMethod'] == 'LowDibbsCredit') {

            } else if($params['owner_amount'] > 0 && $params['paymentMethod'] == 'DibbsCredit') {
                if($custCredits > 0) {
                    if($custCredits > $params['owner_amount']) {
                        /*$oCustomersModel->insert([
                            "credits" => $custCredits - $params['owner_amount']
                        ], $params['customer_id']);*/
                    } else {
                        $params['owner_amount'] = $custCredits;
                        /*$oCustomersModel->insert([
                            "credits" => 0
                        ], $params['customer_id']);*/
                    }
                } else {
                    $params['owner_amount'] = 0;
                }
                /*if($custCredits > 0) {

                } elseif ($custCredits < $params['owner_amount']) {
                    $retData["message"] = $oLangsModel->findByPK("55", "lang_en")["lang_en"];
                    return $retData;
                } else {
                    $oCustomersModel->insert([
                        "credits" => $custCredits - $params['owner_amount']
                    ], $params['customer_id']);
                }*/
            }
        }
        $oOrderPaymentsModel = new \models\OrderPaymentsModel();
        if($params['owner_amount'] > 0 && ($params['paymentMethod'] == 'LowDibbsCredit' || $params['paymentMethod'] == 'DibbsCredit')) {
            if($params['dibbs_credit'] > 0) {
                if($custCredits >= $params['dibbs_credit']) {
                    
                } else {
                    $retData["message"] = $oLangsModel->findByPK("55", "lang_en")["lang_en"];
                    return $retData;
                } 
            } 
        }
        $params['remaining_amount'] = ($params['total_price'] - $params['owner_amount']);
        if (empty($params['order_id'])) {
            $oUserOrdersModel = new \models\UserOrdersModel();
            $userOrderId = $oUserOrdersModel->insert([
                "customer_id" => $params['customer_id'],
                "total_price" => $params['total_price'],
                "owner_amount" => $params['owner_amount'],
                "remaining_amount" => $params['remaining_amount'],
                "added_on" => date("Y-m-d H:i:s")
            ]);
        }
        $oProductsModel = new \models\ProductsModel();
        for ($o = 0; $o < COUNT($params['product_list']); $o++) {
            $prodDetails = $oProductsModel->getProductDetails($params['product_list'][$o]['product_id']);
            /* $oStoresModel = new \models\StoresModel();
              $storeDetails = $oStoresModel->findByPK($prodDetails['store_id'], "user_id"); */
            $params['remaining_amount'] = $params['product_list'][$o]['total_price'] - $params['product_list'][$o]['owner_amount'];
            $insertArr = [
                "customer_id" => $params['customer_id'],
                "store_id" => $prodDetails['store_id'],
                "total_price" => $params['product_list'][$o]['total_price'],
                "discount" => $params['product_list'][$o]['discount'] > 0 ? $params['product_list'][$o]['discount'] : 0,
                "is_coupon_applied" => !empty($params['is_coupon_applied']) ? $params['is_coupon_applied'] : "N",
                "coupon" => !empty($params['coupon']) ? $params['coupon'] : NULL,
                "coupon_number" => !empty($params['coupon_number']) ? $params['coupon_number'] : NULL,
                "owner_amount" => $params['product_list'][$o]['owner_amount'],
                "remaining_amount" => $params['remaining_amount']//$params['product_list'][$o]['total_price'] - $params['product_list'][$o]['owner_amount'],
            ];
            if (!empty($params['order_id'])) {
                $insertArr['updated_on'] = date("Y-m-d H:i:s");
                $insertArr['updated_by'] = $params['customer_id'];
            } else {
                $insertArr['parent_id'] = $userOrderId;
                $insertArr['added_on'] = date("Y-m-d H:i:s");
                $insertArr['added_by'] = $params['customer_id'];
                $insertArr['ip_address'] = \helpers\Common::getIP();
            }
            $order_id = $this->insert($insertArr, $params['order_id']);
            if (!empty($order_id)) {
                if($params['owner_amount'] > 0 && ($params['paymentMethod'] == 'LowDibbsCredit' || $params['paymentMethod'] == 'DibbsCredit')) {
                    if($params['dibbs_credit'] > 0) {
                        if($custCredits >= $params['dibbs_credit']) {
                            if($custCredits > $params['owner_amount']) {
                                $oCustomersModel->insert([
                                    "credits" => $custCredits - $params['owner_amount']
                                ], $params['customer_id']);
                            } else {
                                $oCustomersModel->insert([
                                    "credits" => 0
                                ], $params['customer_id']);
                            }
                            $oOrderPaymentsModel->insert([
                                "order_id" => $order_id,
                                "method" => "DibbsCredits",
                                "amount" => $params['dibbs_credit'],
                                "added_on" => date("Y-m-d H:i:s"),
                                "added_by" => $params['customer_id'],
                            ]);
                        } 
                    } 
                    if(!empty($params['stripe_token'])) {
                        $cardPayment = $params['owner_amount'] - $params['dibbs_credit'];
                        $checkPayment = \helpers\Common::checkPayment(['order_id' => $order_id, 'payment_method' => $params['stripe_token'], 'amount' => $cardPayment * 100]);
                        if(!empty($checkPayment['status']) && $checkPayment['status'] == 'requires_confirmation') {
                            $confirmationId = $checkPayment['id'];
                            $confirmPayment = \helpers\Common::confirmPayment(['order_id' => $order_id, 'payment_method' => $params['stripe_token'], 'payment_id' => $confirmationId]);
                            if(!empty($confirmPayment['status']) && $confirmPayment['status'] == 'succeeded') {
                                $oOrderPaymentsModel->insert([
                                    "order_id" => $order_id,
                                    "method" => "DibbsCredits",
                                    "amount" => $params['dibbs_credit'],
                                    "added_on" => date("Y-m-d H:i:s"),
                                    "added_by" => $params['customer_id'],
                                ]);
                            } 
                        }
                    }
                }
                $oOrderItemsModel = new \models\OrderItemsModel();
                $oOrderItemsModel->update(["fields" => "status = ?, updated_on = ?, updated_by = ? ", "whereClause" => "order_id = ? AND status = ? ", "whereParams" => ["ssiis", "deleted", date("Y-m-d H:i:s"), $params['user_id'], $order_id, "active"]]);

                $insertArrV = [
                    "order_id" => $order_id,
                    "product_id" => $params['product_list'][$o]['product_id'],
                    "variation_id" => $params['product_list'][$o]['variation_id'],
                    "quantity" => $params['product_list'][$o]['quantity'],
                    "unit_price" => $params['product_list'][$o]['unit_price'],
                    "total_price" => $params['product_list'][$o]['total_price'],
                    "added_on" => date("Y-m-d H:i:s"),
                    "added_by" => $params['customer_id']
                ];
                $oOrderItemsModel->insert($insertArrV);
                
                $oCustomersModel = new \models\CustomersModel();
                $customerData = $oCustomersModel->findByPK($params['customer_id']);
                $oStoresModel = new \models\StoresModel();
                $storeData = $oStoresModel->findByPK($prodDetails['store_id']);
                $oUsersModel = new \models\UsersModel();
                $storeOwnerData = $oUsersModel->findByPK($storeData['user_id']);
                $oAppConfigModel = new \models\AppConfigModel();
                $support_email = $oAppConfigModel->find(["fields" => "field_value", "whereClause" => "field_name = ? AND status = ? ", "whereParams" => ["ss", "support_email", "active"]])['field_value'];
                $mailParams = [
                    "emailAddress" => $storeOwnerData['email'],
                    "subject" => "You have received a new order on ".DOMAIN_NAME . "! From " . $customerData['first_name'],
                    "message" => "<b>Dear ".$storeOwnerData['user_name'].",</b><br/><br/>"
                    . "You have received a new order on  ".DOMAIN_NAME." from ".$customerData['first_name'].".<br/>"
                    . "Details are as follows:<br/><br/>"
                    . "<b>Email</b>: ".$customerData['email']."<br/>"
                    . "<b>Name</b>: " . $customerData['first_name'] . " " . $customerData['last_name'] ."<br/>"
                    . "<b>Order Id</b>: ".$order_id."<br/><br/>"
                    . "Please remember to ask the customer for the balance owed and redeem the deal by inputting the unique redeem code that you created in your profile..<br/><br/>"
                    . "<b>Thank you</b><br/>"
                    . "The ".DOMAIN_NAME." Team",
                    "cc" => "",
                    "bcc" => $support_email.",",
                    "replyTo" => $support_email,
                ];
                \helpers\Common::sendMail($mailParams);
                $mailParamsCust = [
                    "emailAddress" => $customerData['email'],
                    "subject" => "Congrats! You just called ".DOMAIN_NAME." on a deal.",
                    "message" => "<b>Dear ".$customerData['first_name'].",</b><br/><br/>"
                    . "Congrats! You just called ".DOMAIN_NAME." on a deal.<br/>"
                    . "Your Order Details are as followed:<br/><br/>"
                    . "<b>Order Id</b>: ".$order_id."<br/>"
                    . "<b>Total Order Amount</b>: $".$params['total_price']."<br/>"
                    . "<b>Amount Paid</b>: $".$params['owner_amount']."<br/>"
                    . "<b>Amount Due</b>: $".$params['remaining_amount']."<br/><br/>"
                    . "When you’re ready, visit the business that corresponds with your order and redeem it. Please make sure to make an appointed if necessary, and allow the merchant to redeem the deal once you’re ready to pay the balance.<br/><br/>"
                    . "<b>Thank you,</b><br/>"
                    . "The ".DOMAIN_NAME." Team",
                    "cc" => "",
                    "bcc" => $support_email.",",
                    "replyTo" => $support_email,
                ];
                \helpers\Common::sendMail($mailParamsCust);
            }
        }
        $retData["code"] = "11";
        $retData["data"] = $order_id;
        $retData["status"] = "Y";
        $retData["message"] = $oLangsModel->findByPK("25", "lang_en")["lang_en"];
        return $retData;
    }

    public function orderPayment($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['order_id'])) {
            $retData["message"] = $oLangsModel->findByPK("50", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['amount'])) {
            $retData["message"] = $oLangsModel->findByPK("51", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['method'])) {
            $retData["message"] = $oLangsModel->findByPK("52", "lang_en")["lang_en"];
            return $retData;
        }
        $orderData = $this->findByPK($params['order_id']);
        if (empty($orderData)) {
            $retData["message"] = $oLangsModel->findByPK("26", "lang_en")["lang_en"];
            return $retData;
        }
        $insertArr['payment_method_id'] = $params['method'];
        $this->insert($insertArr, $params['order_id']);
        
        $checkPayment = \helpers\Common::checkPayment(['order_id' => $params['order_id'], 'payment_method' => $params['method'], 'amount' => $params['amount'] * 100]);
        if(!empty($checkPayment['status']) && $checkPayment['status'] == 'requires_confirmation') {
            $confirmationId = $checkPayment['id'];
            $confirmPayment = \helpers\Common::confirmPayment(['order_id' => $params['order_id'], 'payment_method' => $params['method'], 'payment_id' => $confirmationId]);
            if(!empty($confirmPayment['status']) && $confirmPayment['status'] == 'succeeded') {
                $insertArr['payment_confirmation_id'] = $confirmPayment['id'];
                $insertArr['payment_received'] = ($confirmPayment['amount_received'] / 100);
                $insertArr['payment_status'] = "paid";
                $insertArr['payment_updated_on'] = date("Y-m-d H:i:s");
                $this->insert($insertArr, $params['order_id']);

                $retData["code"] = "11";
                $retData["status"] = "Y";
                $retData["message"] = $oLangsModel->findByPK("54", "lang_en")["lang_en"];
                return $retData;
            }
        }
        $retData["message"] = $oLangsModel->findByPK("53", "lang_en")["lang_en"];
        return $retData;
    }

    public function myOrders($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        $offset = (!empty($params['start']) ? $params['start'] : 0);
        $rows = (!empty($params['rows']) ? $params['rows'] : 20);
        $searchArr = ["fields" => "order_id", "whereClause" => " 1 ", "whereParams" => [""]];
        if (!empty($params['customer_id'])) {
            $searchArr["whereClause"] .= " AND customer_id = ? ";
            $searchArr["whereParams"][0] .= "i";
            $searchArr["whereParams"][] = $params['customer_id'];
        }
        if (!empty($params['status'])) {
            $searchArr["whereClause"] .= " AND status = ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = $params['status'];
        } else {
            $searchArr["whereClause"] .= " AND status <> ? ";
            $searchArr["whereParams"][0] .= "s";
            $searchArr["whereParams"][] = "deleted";
        }
        $searchArr["whereClause"] .= " ORDER BY order_id DESC ";
        $prodCount = $this->count($searchArr);
        $searchArr["whereClause"] .= " LIMIT ?, ? ";
        $searchArr["whereParams"][0] .= "ii";
        $searchArr["whereParams"][] = $offset;
        $searchArr["whereParams"][] = $rows;
        $prodData = $this->findAll($searchArr);
        for ($p = 0; $p < COUNT($prodData); $p++) {
            $prodData[$p] = $this->getOrderDetails($prodData[$p]['order_id']);
        }
        if (!empty($prodData)) {
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["data"]['orders'] = $prodData;
            $retData["data"]['count'] = $prodCount;
            $retData["message"] = $oLangsModel->findByPK("13", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("26", "lang_en")["lang_en"];
        }
        return $retData;
    }

    public function redeemOrder($params) {
        $retData = ['code' => '00', 'status' => 'N'];
        $oLangsModel = new \models\LangsModel();
        if (empty($params['order_id'])) {
            $retData["message"] = $oLangsModel->findByPK("35", "lang_en")["lang_en"];
            return $retData;
        }
        if (empty($params['redeem_code'])) {
            $retData["message"] = $oLangsModel->findByPK("36", "lang_en")["lang_en"];
            return $retData;
        }
        $orderData = $this->findByPK($params['order_id']);
        if (!empty($orderData)) {
            if ($orderData['is_redeemed'] == 'Y') {
                $retData["message"] = $oLangsModel->findByPK("37", "lang_en")["lang_en"];
                return $retData;
            }
            $oStoresModel = new \models\StoresModel();
            $storeData = $oStoresModel->findByPK($orderData['store_id'], "redeem_code");
            if ($storeData['redeem_code'] != $params['redeem_code']) {
                $retData["message"] = $oLangsModel->findByPK("39", "lang_en")["lang_en"];
                return $retData;
            }
            $this->insert(['is_redeemed' => "Y", "status" => "redeemed"], $params['order_id']);
            $retData["status"] = "Y";
            $retData["code"] = "11";
            $retData["message"] = $oLangsModel->findByPK("38", "lang_en")["lang_en"];
        } else {
            $retData["message"] = $oLangsModel->findByPK("26", "lang_en")["lang_en"];
        }
        return $retData;
    }

}
