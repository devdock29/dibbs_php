<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use azharORM\MySQL\MyDBClass;
//use azharORM\MongoDB\MongoDBClass;
use azharFactory\AzharFactory;
use azharFramework\Session;
use AzharConstants;

class AzharFramework {

    private $defaultController = "Home";
    private $defaultAction = "index";
    private $defaultView = "home";
    private $controllerPostfix = "Controller";
    private $modelPostfix = "Model";
    private $actionPostfix = "Action";
    private $oRequest;
    private $baseURL;
    private $DIR; //Application Directory which is initiating framework class

    public function __construct($params = array()) {
        if (isset($params['DIR'])) {
            $this->DIR = $params['DIR'];
            AzharConfigs::setBaseDir($this->DIR);
        } else {
            die("Unable to load directory");
        }
        if (isset($params['azharConfigs'])) {
            AzharConfigs::set($params['azharConfigs']);
        } else {
            die("Unable to load Configuration");
        }
        $errorLogsMod = (AzharConfigs::getNthKey("errorLogs", "debug") == 0 ? 0 : 1);
        if ($errorLogsMod == 0) {
            error_reporting(E_ALL);
        }
        AzharFactory::init(array(
            "errorLogsMod" => $errorLogsMod,
            "logFilePath" => (AzharConfigs::getNthKey("errorLogs", "logsFilePath") ? AzharConfigs::getNthKey("errorLogs", "logsFilePath") : ''),
        ));
        $this->baseURL = (isset($params['baseURL']) ? $params['baseURL'] : '');
        AzharConfigs::setBaseURL($this->baseURL);
        $this->addServerices();
    }

    public function addRoutes($routes) {
        if (!empty($routes)) {
            routes\Router::loadRoutes($routes);
        }
    }

    public function addServerices() {
        $this->loadDBs();
        //$this->loadNOSql();
        AzharFactory::add(AzharConstants::SESSION, new Session());
        if (AzharConfigs::getNthKey('cache', 'rzCache') && AzharConfigs::getNthKey('cache', 'enable')) {
            AzharFactory::add(AzharConstants::CACHE, new \cache\AzharCache());
        }
        // $this->oRequest = new Request($this->baseURL);
        // AzharFactory::add(AzharConstants::REQUEST, $this->oRequest);
    }

    public function loadNOSql() {
        $dbs = AzharConfigs::get(AzharConstants::NOSQL);
        if ($dbs === null) {
            return;
        }
        foreach ($dbs as $dbAlias => $dbCredentials) {
            $oMongoDBClass = new MongoDBClass(array("host" => $dbCredentials["host"],
                "dbName" => $dbCredentials["dbName"],
                "userName" => $dbCredentials["userName"],
                "password" => $dbCredentials["password"],
                    //"port" => $dbCredentials["port"]
            ));
            if ($dbCredentials["isDefault"] == 1) {
                (!defined("DEFAULT_NO_SQL") ? define("DEFAULT_NO_SQL", $dbAlias) : '');
            }
            AzharFactory::add(AzharConstants::NOSQL . $dbAlias, $oMongoDBClass);
        }
    }

    public function loadDBs() {
        $dbs = AzharConfigs::get('db');
        if ($dbs !== null) {
            foreach ($dbs as $dbAlias => $dbCredentials) {
                $oMyDBClass = new MyDBClass(array("host" => $dbCredentials["host"],
                    "dbName" => $dbCredentials["dbName"],
                    "userName" => $dbCredentials["userName"],
                    "password" => $dbCredentials["password"],
                    "port" => (isset($dbCredentials["port"]) ? $dbCredentials["port"] : ini_get("mysqli.default_port"))
                ));
                if ($dbCredentials["isDefault"] == 1) {
                    (!defined("DEFAULT_DATABASE") ? define("DEFAULT_DATABASE", $dbAlias) : '');
                }
                (!defined($dbAlias) ? define($dbAlias, $dbAlias) : '');
                AzharFactory::add($dbAlias, $oMyDBClass);
            }
        }
    }

    public function closeDBs() {
        $dbs = AzharConfigs::get('db');
        if ($dbs !== null) {
            foreach ($dbs as $dbAlias => $dbCredentials) {
                $oDBConn = AzharFactory::get($dbCredentials["dbName"]);
                if ($oDBConn instanceof \mysqli) {
                    $oDBConn->close();
                }
            }
        }
    }

    public function loadFile($fileName) {
        $_fileName = $fileName . ".php";
        if (is_file($_fileName)) {
            include $_fileName;
            return true;
        }
    }

    private function getClassPath($className) {
        return $this->DIR . "/" . str_ireplace("\\", "/", $className);
    }

    public function nasLoader() {
        spl_autoload_register(function ($className) {
            $fileName = $this->getClassPath($className);
            return $this->loadFile($fileName);
        });
    }

    public function console($params) {
        try {
            if (!isset($params['argv']) || !isset($params['argc'])) {
                throw new \azharORM\azharException("Exception: No Argument Found");
            }
        } catch (\azharORM\azharException $ex) {
            die("\n" . $ex->getMessage() . "\n");
        }
        $this->nasLoader();
        $this->oRequest = new cRequest($params);
        AzharFactory::add(AzharConstants::REQUEST, $this->oRequest);
        $this->triggerAction();
    }

    private function triggerAction() {
        $proposedController = ucfirst($this->oRequest->urlElements[0]);
        $modelName = $controllerName = $this->defaultController;
        $viewName = $this->defaultView;
        $actionName = $this->defaultAction . $this->actionPostfix;
        if (trim($proposedController) != '') {
            $modelName = $controllerName = $proposedController;
            $viewName = $this->oRequest->urlElements[0];
        }
        $controllerNamespace = "controllers\\";
        if (routes\Info::get('controllerNamespace') !== null) {
            $controllerNamespace .= str_replace("/", "\\", routes\Info::get('controllerNamespace'));
        }
        $controllerName = $controllerNamespace . $controllerName . $this->controllerPostfix;
        if (class_exists($controllerName)) {
            $oController = new $controllerName ();
            /* Adding current controller's object to factory in order
             * to use it on demand.e.g; lang\Lang is using current
             * controllers->getLangdIndex()
             * in order to get name of the page index lable.
             * */
            AzharFactory::add("__controller__", $oController);
            $oController->setViewName(strtolower($viewName));
            $oController->setModel($modelName . $this->modelPostfix);
            if (isset($this->oRequest->urlElements[1]) && trim($this->oRequest->urlElements[1]) != '') {
                $actionName = strtolower($this->oRequest->urlElements[1]) . $this->actionPostfix;
            }
            $oController->setActionName($actionName);
            $oController->beforeAction();
            if (method_exists($oController, $actionName)) {
                $result = $oController->$actionName();
            } else {
                $result = $oController->errorAction(array('action' => strtolower($this->oRequest->urlElements[1])));
            }
            $oController->afterAction();
        } else {
            $catchAllController = "controllers\CatchAllController";
            $oController = new $catchAllController($this->oRequest->urlElements[0]);
            AzharFactory::add("__controller__", $oController);
            $oController->setViewName('catchall');
            $oController->beforeAction();
            $result = $oController->indexAction();
            $oController->afterAction();
        }
        $this->shutdown();
    }

    public function run() {
        $this->nasLoader();
        $this->oRequest = new Request($this->baseURL);
        AzharFactory::add(AzharConstants::REQUEST, $this->oRequest);
        $this->triggerAction();
    }

    public function shutdown() {
        $this->closeDBs();
    }

}
