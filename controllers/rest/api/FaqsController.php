<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\rest\api;

class FaqsController extends AppController {

    public function searchAction() {
        $post = $this->obtainPost();
        $oFaqsModel = new \models\FaqsModel();
        $retData = $oFaqsModel->getList($post);

        $this->response = $retData;
        echo $this->buildResponse($retData['status'], $this->response, $retData['code']);
    }
    
}
