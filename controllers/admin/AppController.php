<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers\admin;

class AppController extends \azharFramework\azharController {

    protected $viewsFolderName = "views/default";
    
    public function _construct() {
        $this->setTwigViewsAdditionalPath("admin");
    }

    public function beforeRender() {
        $this->setTwigViewsAdditionalPath("admin");
    }

    public function beforeAction() {
        $oMeta = $this->model('MetaModel', true);
        $this->meta($oMeta);
    }

    public function errorAction($params = array()) {
        $this->redirect(array("url" => $this->baseUrl() . "site/error?e=" . (isset($params['action']) ? $params['action'] : "anf"), "data" => []));
    }

    public function afterAction() {
        $curIP = \helpers\Common::getIP();
        $allowedIPs = array();

        if (in_array($curIP, $allowedIPs) || 1) {
            $get = $this->obtainGet();
            if ($get !== null) {
                if (isset($get['debugSQL']) && $get['debugSQL'] == "x") {
                    $this->state()->remove("debugSQL");
                } elseif ((isset($get['debugSQL']) && $get['debugSQL'] == "st") || $this->state()->get("debugSQL") !== null) {
                    $this->state()->set("debugSQL", "st");
                    $this->monitor();
                }
            }
        }
    }

}
