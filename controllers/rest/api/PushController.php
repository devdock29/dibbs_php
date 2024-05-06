<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class PushController extends StateController {

    public function addGcmAction() {
        $post = $this->obtainPost();
        $oUsersGcmModel = new \models\UsersGcmModel();
        $post['customer_id'] = $this->getAzharConfigsByKey("USER_ID");
        $retData = $oUsersGcmModel->addGcm($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
}
