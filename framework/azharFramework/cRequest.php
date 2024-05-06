<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use azharFramework\AzharFactory as AzharFactory;
use azharFramework\Security as Security;
use AzharConstants;
use \azharFramework\routes\Info;

class cRequest extends Request {

    public function __construct($params) {
        $this->security = new Security();
        $this->requestMethod = 'CONSOLE';
        $this->manageGET($params['argv'], $params['argc']);
        $this->triggerRoute();
    }

    private function triggerRoute() {
        try {
            $get = AzharFactory::get(AzharConstants::GET);
            if (isset($get['route']) && $get['route'] != '' && stripos($get['route'], "@") !== false) {
                $_data = explode("@", $get['route']);
                routes\Router::setRouteData($_data);
                //setting action name at 0th place
                $urlParts[0] = $_data[0];
                $urlParts[1] = $_data[1];

                Info::set("controller", $_data[0]);
                Info::set("action", $_data[1]);
                Info::set("requestMethod", "GET");
                Info::set("controllerNamespace", "crons/");
                $this->urlElements = $this->security->NcheckValues($urlParts);
                $this->setURLElementCount(count($this->urlElements));
                return true;
            }
            throw new \azharORM\azharException('Exception: Route is missing or invalid, correct format is route=controllerName@actionName');
        } catch (\azharORM\azharException $ex) {
            die("\n" . $ex->getMessage() . "\n");
        }
    }

    private function manageGET($argv, $argc) {
        if ($argc > 1) {
            array_shift($argv);
            $getArray = array();
            foreach ($argv as $value) {
                $param = explode("=", $value);
                if (isset($param[1])) {
                    $getArray[$param[0]] = $param[1];
                }
            }
            $getArray = $this->security->NcheckValues($getArray);
            if (AzharFactory::get(AzharConstants::GET)) {
                $getArray = array_merge(AzharFactory::get(AzharConstants::GET), $getArray);
            }
            AzharFactory::add(AzharConstants::GET, $getArray);
        }
    }

}
