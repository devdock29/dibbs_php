<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class OrderPaymentsModel extends AppModel {

    protected $relation = 'order_payments';
    protected $pk = 'auto_id';

    public function getOrderPayments($order_id) {
        $payments = $this->findAll([
            "fields" => "method, amount", "whereClause" => " order_id = ? ", "whereParams" => ["i", $order_id]
        ]);
        return $payments;
    }
}
