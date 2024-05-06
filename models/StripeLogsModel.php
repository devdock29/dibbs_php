<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

class StripeLogsModel extends AppModel {
    protected $relation = 'stripe_logs';
    protected $pk = 'id';
    
    public function addLog($params) {
        $inArr = $params;
        $inArr['created_on'] = date("Y-m-d H:i:s");
        $this->insert($inArr);
    }
}
