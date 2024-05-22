<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class UserController extends AppController {
    
    public function forgotPasswordAction() {
        $data = array();
        if($this->isPOST()) {
            $post = $this->obtainPost();
            $oUsersModel = new \models\UsersModel();
            $oUsersModel->register_user($post);
        }
        $metaData['title'] = 'Forgot Password on '.DOMAIN_BRAND_NAME;
        $metaData['keywords'] = 'forgot,password,recovery,'.DOMAIN_BRAND_NAME;
        $metaData['description'] = 'Forgot Password on '.DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('forgot_password', $data);
    }
    
    public function logoutAction() {
        $userData = $this->getState('adminUser');
        $oSessionsModelo = new \models\SessionsModel();
        $oSessionsModelo->delete([
            "whereClause" => "user_id = ? AND user_type = ? ",
            "whereParams" => ["is", $userData['user_id'], "admin"],
        ]);
        $this->setState('adminUser', '');
        $this->redirect(['url' => ADMIN_URL, 'data' => []]);
    }
}
