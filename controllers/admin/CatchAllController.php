<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class CatchAllController extends AppController {

    private $controllerToFind;

    public function __construct($controllerToFind) {
        //echo $controllerToFind . "<br />"; exit;
        $this->controllerToFind = $controllerToFind;
    }

    public function indexAction() {
        $this->redirect(array('url' => $this->baseUrl() . 'site/error/?e=cnf_' . $this->controllerToFind, "data" => []));
    }

}
