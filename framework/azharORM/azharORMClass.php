<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM;

use azharORM\azharORMInterface as azharORMInterface;
use azharORM\azharException as azharException;
use azharLogger\azharLoggerClass as azharLoggerClass;

abstract class azharORMClass implements azharORMInterface {

    protected $initialized = false;
    protected $required = array('host', 'dbName');

    public function __construct($dbCredentials = array()) {

        if (isset($dbCredentials['errorLogsMod'])) {
            if (isset($dbCredentials['logFilePath'])) {
                $this->errorLogsFilePath = $dbCredentials['logFilePath'];
                $this->errorLogsMod = ($dbCredentials['errorLogsMod'] == 1 ? 1 : 0);
                azharLoggerClass::initLogger(array("errorLogsMod" => $this->errorLogsMod, "logFilePath" => $this->errorLogsFilePath));
            }
        }
        if (!empty($dbCredentials)) {
            $this->initDB($dbCredentials);
        }
    }

    public function initDB($dbCredentials) {
        try {
            foreach ($this->required as $key) {
                if (array_key_exists($key, $dbCredentials)) {
                    $this->dbCredentials[$key] = $dbCredentials[$key];
                } else {
                    throw new azharException("Missing Key is '" . $key . "'");
                }
            }
            $this->setInitialized();
        } catch (azharException $ex) {
            azharLoggerClass::logIt(array("type" => "Configuration", "contents" => $ex->getStackTrace()));
        } catch (Exception $ex) {
            azharLoggerClass::logIt(array("type" => "Configuration", "contents" => $ex->getMessage()));
        }
    }

    protected function validQueryCheck($params) {
        $this->isInitialized();
        if (!isset($params['qry'])) {
            throw new azharException("Query is Missing");
        }
        $queryTokens = explode(" ", trim($params['qry']));
        if (isset($params['queryTypeIndex'])) {
            if (stripos($params['qry'], $params['querytType']) === false)
                throw new azharException("Not a valid {$params['querytType']} query");
        }
        else {
            if (strtoupper($queryTokens[0]) != $params['querytType']) {
                throw new azharException("Not a valid {$params['querytType']} query");
            }
        }
        return $queryTokens[2]; //getting table name
    }

    protected function setInitialized($val = true) {
        $this->initialized = $val;
    }

    public function getInitialized() {
        return $this->initialized;
    }

    public function isInitialized() {
        if (!$this->getInitialized())
            throw new azharException("DB Class not initialized yet, try initDB method first.");
    }

}
