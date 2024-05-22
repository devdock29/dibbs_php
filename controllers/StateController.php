<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class StateController extends AppController {

    public function beforeAction() {
        $userData = $this->getUserState();
        if (empty($userData)) {
            $data['error'] = "Session Expired, Please re-login";
            $this->setGuest(true);
            $this->setState('user', '');
            $this->redirect(['url' => SITE_URL, 'data' => $data]);
        } else {
            $oSessionsModel = new \models\SessionsModel();
            $validated = $oSessionsModel->findByFieldString('session_id', $userData['token']);
            if (empty($validated)) {
                $data['error'] = "Session Expired, Please re-login";
                $this->setGuest(true);
                $this->setState('user', '');
                $this->redirect(['url' => SITE_URL, 'data' => $data]);
            }
        }
        $this->addToConfig("USER_ID", $userData['user_id']);
        $this->addToConfig("STORE_ID", $userData['store_id']);
        
        $oMeta = $this->model('MetaModel', true);
        $this->meta($oMeta);
    }

}
