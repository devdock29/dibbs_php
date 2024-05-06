<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\services;

class HomeController extends \controllers\AppController {

    public function contactUsAction(){
        $params = [];
    	$params = $this->obtainPost();
        
        $oContactUsModel = new \models\ContactUsModel();
        $result = $oContactUsModel->insertData($params);
        if ($result) {
            $params['type'] = 'contactus';
            $response = \helpers\Common::sendMail($params);
            if ($response) {
                header('Content-Type:application/json');
                echo json_encode($result);
                //return $this->jsonResponse(true, 'Email has sent successfully', 200);
            }
        }
    }
}

