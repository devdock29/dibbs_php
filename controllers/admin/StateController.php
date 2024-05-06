<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class StateController extends AppController {

    public function beforeAction() {
        $userData = $this->getState('adminUser');
        if (empty($userData)) {
            $data['error'] = "Session Expired, Please re-login";
            $this->setGuest(true);
            $this->setState('adminUser', '');
            $this->redirect(['url' => ADMIN_URL, 'data' => $data]);
        } else {
            $oSessionsModel = new \models\SessionsModel();
            $validated = $oSessionsModel->findByFieldString('session_id', $userData['token']);
            if (empty($validated)) {
                $data['error'] = "Session Expired, Please re-login";
                $this->setGuest(true);
                $this->setState('adminUser', '');
                $this->redirect(['url' => ADMIN_URL, 'data' => $data]);
            }
        }
        $oMeta = $this->model('MetaModel', true);
        $this->meta($oMeta);
    }

}
