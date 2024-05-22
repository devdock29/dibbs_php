<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class HomeController extends AppController {
    
    public function indexAction() {
        $data = array();
        $userData = $this->getUserState();
        if (!empty($userData)) {
            $this->redirect(['url' => SITE_URL."dashboard", 'data' => $data]);
        } 
        if($this->isPOST()) {
            $post = $data['posted'] = $this->obtainPost();
            $oUsersModel = new \models\UsersModel();
            $user_registered = $oUsersModel->login($post);
            if($user_registered['status'] == 'success') {
                $this->redirect(['url' => SITE_URL."dashboard", 'data' => $data]);
            } else {
                $data['error'] = $user_registered['error'];
            }
        }
        $metaData['title'] = 'Login Now on '.DOMAIN_BRAND_NAME;
        $metaData['keywords'] = 'regietr,'.DOMAIN_BRAND_NAME;
        $metaData['description'] = 'Login now on '.DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('index', $data);
    }
}
