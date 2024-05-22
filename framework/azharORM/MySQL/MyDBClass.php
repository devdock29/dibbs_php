<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM\MySQL;

use \azharORM\azharException as nasException;
use azharLogger\azharLoggerClass as azharLoggerClass;
use azharFramework\SQLMonitor;
use \azharFramework\helpers\AzharString;

class MyDBClass extends \azharORM\azharORMClass {

    private $oMySQLi;
    private $error;
    protected $required = array('host', 'dbName', 'userName', 'password', 'port');

    public function __construct($dbCredentials = array()) {
        parent::__construct($dbCredentials);
    }

    public function dbConn() {
        try {
            $this->isInitialized();
            //enabling mysqli exceptions rather than warnings
            mysqli_report(MYSQLI_REPORT_STRICT);
            $this->oMySQLi = new \mysqli(
                $this->dbCredentials['host'], $this->dbCredentials['userName'], $this->dbCredentials['password'], $this->dbCredentials['dbName'], $this->dbCredentials['port']
            );
        } catch (\Exception $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => "Exception: " . $ex->getMessage()));
        }
    }

    public function beginTransaction() {
        return $this->getConnection()->begin_transaction();
    }

    public function commit() {
        return $this->getConnection()->commit();
    }

    public function rollback() {
        return $this->getConnection()->rollback();
    }

    public function close() {
        if ($this->oMySQLi instanceof \mysqli) {
            $this->oMySQLi->close();
        }
    }

    public function getConnection() {
        if ($this->oMySQLi == '') {
            $this->dbConn();
        }
        return $this->oMySQLi;
    }

    public function getQueryBuilder() {
        return new QueryBuilder($this);
    }

    public function find($params) {
        $params['limit'] = 1;
        return $this->findAll($params);
    }

    public function findAll($params) {
        try {
            $params['querytType'] = (isset($params['querytType']) ? $params['querytType'] : "SELECT");
            $this->validQueryCheck($params);
            $array = NULL;
            $limit = (isset($params['limit']) ? $params['limit'] : 0);
            $whereParams = (isset($params['whereParams']) ? $params['whereParams'] : array());
            SQLMonitor::startTimer();
            $oPreparedStatement = $this->setStatement($params['qry'], $whereParams);

            if ($oPreparedStatement != NULL) {
                if ($oPreparedStatement->execute()) {
                    $oPreparedStatement->store_result();
                    $variables = array();
                    $data = array();
                    $meta = $oPreparedStatement->result_metadata();
                    while ($field = $meta->fetch_field()) {
                        $variables[] = &$data[$field->name];
                    }

                    call_user_func_array(array($oPreparedStatement, 'bind_result'), $variables);
                    $i = 0;
                    while ($oPreparedStatement->fetch()) {
                        $array[$i] = array();
                        foreach ($data as $k => $v) {
                            if (isset($params['fetchType']) && $params['fetchType'] == "ASSOC") {
                                $array[$i][] = stripslashes($v);
                            } else {
                                $array[$i][$k] = stripslashes($v);
                            }
                        }$i++;
                    }
                    if ($limit == 1) {
                        $array = $array[0];
                    }
                    $oPreparedStatement->close();
                    SQLMonitor::stopTimer($params['qry'], $whereParams);
                } else {
                    $errMsg = $oPreparedStatement->error;
                    $oPreparedStatement->close();
                    throw new nasException($errMsg);
                }
            }
        } catch (nasException $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $ex->getStackTrace() . "\n" . $params['qry']));
            return false;
        } catch (\Exception $e) {
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
            $array = false;
        }
        return $array;
    }

    public function setStatement($query, $param) {
        SQLMonitor::startStatementTimer();
        $stmt = $this->executeMySQLiMethod('prepare', $query);
        if (false === $stmt) {
            throw new \Exception();
        }
        $ref = new \ReflectionClass('mysqli_stmt');
        if (count($param) != 0) {
            $method = $ref->getMethod('bind_param');
            $refs = array();
            foreach ($param as $key => $value)
                $refs[$key] = &$param[$key];
            if (false === $method->invokeArgs($stmt, $refs)) {
                azharLoggerClass::logIt(array("type" => "SQL", "contents" => "Exception: " . $stmt->error . "\n" . $query));
                if ($stmt != null) {
                    $stmt->close();
                }
            }
        }
        SQLMonitor::stopStatementTimer();
        return $stmt;
    }

    public function save($params) {
        $saveStatus = false;
        try {
            $oPreparedStatement = $this->setStatement($params['qry'], $params['data']);
            if ($oPreparedStatement != NULL) {
                SQLMonitor::startTimer();
                if ($oPreparedStatement->execute()) {
                    if (isset($params['update']) && $params['update'] == 'y') {
                        $saveStatus = true;
                    } else {
                        $saveStatus = $oPreparedStatement->insert_id;
                        if ($saveStatus == 0) {//if no auto increment value set
                            $saveStatus = true;
                        }
                    }
                    $oPreparedStatement->close();
                    SQLMonitor::stopTimer($params['qry'], $params['data']);
                } else {
                    throw new nasException($oPreparedStatement->error);
                }
            } else {
                throw new \Exception();
            }
        } catch (nasException $e) {
            $oPreparedStatement->close();
            $this->setError($e->getStackTrace() . "\n" . $params['qry']);
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $e->getStackTrace() . "\n" . $params['qry']));
        } catch (\Exception $e) {
            $this->setError($this->getMySQLiError() . "\n" . $params['qry']);
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
        }
        return $saveStatus;
    }

    private function setError($error) {
        $this->error = $error;
    }

    public function getError() {
        return $this->error;
    }

    public function saveByQuery($params) {
        $result = $this->executeMySQLiMethod('query', $params['qry']);
        if ($result) {
            return true;
        } else {
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
            return false;
        }
    }

    public function insert($params) {
        $params['querytType'] = "INSERT";
        return $this->executePreparedQuery($params);
    }

    public function replace($params) {
        $params['querytType'] = "REPLACE";
        return $this->executePreparedQuery($params);
    }

    public function update($params) {
        $params['querytType'] = "UPDATE";
        return $this->executePreparedQuery($params);
    }

    public function isExist($params) {
        return $this->find($params);
    }

    public function delete($params) {
        $params['querytType'] = "DELETE";
        return $this->executePreparedQuery($params);
    }

    public function alter($params) {
        $params['querytType'] = "ALTER";
        return $this->executePreparedQuery($params);
    }

    public function count($params) {
        try {
            $params['querytType'] = "COUNT";
            $params['queryTypeIndex'] = 1;
            $params['fetchType'] = 'ASSOC';
            self::validQueryCheck($params);
            $row = self::find($params);
            return $row[0];
        } catch (nasException $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $ex->getStackTrace()));
            return false;
        } catch (\Exception $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
            return false;
        }
    }

    private function executePreparedQuery($params) {
        $affectedRows = 0;
        try {
            $tableName = $this->validQueryCheck($params);
            $where = (isset($params['whereParams']) ? $params['whereParams'] : array());
            $oPreparedStatement = $this->setStatement($params['qry'], $where);
            if ($oPreparedStatement != NULL) {
                SQLMonitor::startTimer();
                if ($oPreparedStatement->execute()) {
                    $affectedRows = $oPreparedStatement->affected_rows;
                    SQLMonitor::stopTimer($params['qry'], $where);
                } else {
                    throw new \Exception ();
                }
            } else {
                throw new \Exception ();
            }
        } catch (nasException $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $ex->getStackTrace()));
            return false;
        } catch (\Exception $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
            return false;
        }
        return $affectedRows;
    }

    public function newTable($tableName, $id = NULL) {
        try {
            $this->isInitialized();
            return new MyDBTable($tableName, $id, $this);
        } catch (nasException $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $ex->getStackTrace()));
            return false;
        } catch (\Exception $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "errorCode" => $this->getMySQLiErrorNo(), "contents" => "Exception: " . $this->getMySQLiError() . "\n" . $params['qry']));
            return false;
        }
    }

    public function getTableMeta($tableName, $full = false) {
        $params = array("qry" => "SHOW COLUMNS FROM " . $tableName,
            'querytType' => 'SHOW',
            'queryTypeIndex' => 1
        );
        $metaData = $this->findAll($params);
        if ($full) {
            return $metaData;
        }
        $metaArr = array();
        $metaArr["PK_FIELD"] = "";
        foreach ($metaData as $key => $value) {
            $metaArr[$value['Field']] = $this->getDataTypeAlias($value['Type']);
            if ($value['Key'] == "PRI")
                $metaArr["PK_FIELD"] = $value['Field'];
        }
        return $metaArr;
    }

    private function getDataTypeAlias($type) {
        if (AzharString::startsWith($type, "int") !== false)
            return "i";
        elseif (AzharString::startsWith($type, "double") !== false)
            return "d";
        else
            return "s";
    }

    public function realEscapeString($params) {
        return $this->executeMySQLiMethod('real_escape_string', $params['value']);
    }

    public function executeMySQLiMethod($method, $methodParams) {
        $oConnection = $this->getConnection();
        if ($oConnection instanceof \mysqli) {
            return $oConnection->$method($methodParams);
        } else {
            //throw new azharException('Connection not found');
        }
    }

    public function getMySQLiError() {
        $oConnection = $this->getConnection();
        if ($oConnection instanceof \mysqli) {
            return $this->getConnection()->error;
        }
    }

    public function getMySQLiErrorNo() {
        $oConnection = $this->getConnection();
        if ($oConnection instanceof \mysqli) {
            return $this->getConnection()->errno;
        }
    }

    public function setCharSet() {
        $this->setCharSetUTF8();
    }

    public function setCharSetUTF8() {
        $this->getConnection()->query('SET NAMES utf8');
    }

    public function ping() {
        $oConnection = $this->getConnection();
        if ($oConnection instanceof \mysqli) {
            try {
                if (!$oConnection->ping()) {
                    $this->close();
                }
            } catch (\Exception $ex) {

            }
        }
    }

}
