<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
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
