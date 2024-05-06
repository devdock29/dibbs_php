<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM\MySQL;

use azharORM\azharException as azharException;
use azharLogger\azharLoggerClass as azharLoggerClass;

class MyDBTable {

    private $tableName;
    private $data = array();
    private $dataEdit = array();
    private $isSaved = false;
    private $meta = array();
    private $pkField;
    private $pk;
    private $isEditMode = false;
    private $isLoaded = false;
    private $lastQryErr;
    private $oMyDBClass;
    private $ignore = '';

    public function __construct($tableName, $id, $dbObject) {
        $this->tableName = $tableName;
        $this->setMyDBClass($dbObject);
        $this->meta = $this->getMyDBClass()->getTableMeta($this->tableName);
        $this->setPKField($this->meta['PK_FIELD']);
        if ($id != NULL && $id !== '') {
            $this->setPK($id);
            $this->{$this->pkField} = $id;
            $this->isEditMode = true;
            $this->findByPK();
        }
    }

    public function editMode() {
        return $this->isEditMode;
    }

    public function setMyDBClass($obj) {
        $this->oMyDBClass = $obj;
    }

    public function getMyDBClass() {
        return $this->oMyDBClass;
    }

    public function isLoaded() {
        return $this->isLoaded;
    }

    public function findByPK() {
        $this->data = $this->getMyDBClass()->find(array(
            "qry" => $this->getSelectQuery(),
            "whereParams" => array(
                $this->getDataTypeString($this->getPKField()),
                $this->getPK()))
        );
        if (!empty($this->data))
            $this->isLoaded = true;
    }

    public function loadByCriteria($params) {
        $this->data = $this->getMyDBClass()->find($params);
        if (!empty($this->data)) {
            $this->isLoaded = true;
        }
    }

    private function getSelectQuery() {
        return "SELECT * FROM " . $this->tableName . " WHERE " . $this->getPKField() . "=?";
    }

    public function setPK($id) {
        $this->pk = $id;
    }

    public function getPK() {
        return $this->pk;
    }

    private function getPKField() {
        return $this->pkField;
    }

    private function setPKField($field) {
        $this->pkField = $field;
    }

    private function getDataTypeString($field) {
        if (isset($this->meta[$field])) {
            return $this->meta[$field];
        } else {
            throw new azharException("Invalid field $field.");
        }
    }

    public function addAlias($key, $alias) {
        $this->data[$alias] = (isset($this->data[$key]) ? $this->data[$key] : "");
    }

    public function save() {
        $qry = $types = $columns = $values = "";
        $valuesArr = array();
        $valuesArr[0] = "";
        try {
            if ($this->isEditMode) {
                $qry = "UPDATE " . $this->getTableName() . " SET ";
                $i = 1;
                foreach ($this->dataEdit as $column => $value) {
                    $columns .= "`" . $column . "`" . "=?,";
                    $valuesArr[$i++] = $value;
                    $types .= $this->getDataTypeString($column);
                }

                $columns = substr($columns, 0, -1);
                $qry .= $columns . " WHERE " . $this->getPKField() . "=? LIMIT 1";
                $valuesArr[] = $this->getPK();
                $valuesArr[0] = $types . "i";
                $insertedId = $this->getMyDBClass()->save(array("qry" => $qry, "data" => $valuesArr, "update" => "y"));
                if ($insertedId) {
                    $this->isSaved = true;
                }
            } else {
                $qry = "INSERT {$this->ignore} INTO " . $this->getTableName() . " (";
                $i = 1;
                foreach ($this->data as $column => $value) {
                    $columns .= '`' . $column . '`' . ",";
                    $values .= "?,";
                    $valuesArr[$i++] = $value;
                    $types .= $this->getDataTypeString($column);
                }
                $valuesArr[0] = $types;
                $columns = substr($columns, 0, -1);
                $values = substr($values, 0, -1);
                $qry .= $columns . ") VALUES(" . $values . ")";
                $insertedId = $this->getMyDBClass()->save(array("qry" => $qry, "data" => $valuesArr));
                if ($insertedId) {
                    $this->isSaved = true;
                    $this->data[$this->getPKField()] = $insertedId;
                    $this->setPK($insertedId);
                } else {
                    $this->setError($this->getMyDBClass()->getError());
                }
            }
        } catch (azharException $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => $ex->getStackTrace()));
        } catch (Exception $ex) {
            azharLoggerClass::logIt(array("type" => "SQL", "contents" => self::getConnection()->error . "\n" . $ex->getMessage()));
            $array = false;
        }
    }

    public function isSaved() {
        return $this->isSaved;
    }

    public function getTableName() {
        return $this->tableName;
    }

    public function __set($column, $value) {
        $this->data[$column] = $value;
        $this->dataEdit[$column] = $value;
    }

    public function __get($column) {
        return $this->data[$column];
    }

    public function getDataArray() {
        return $this->data;
    }

    public function setError($error) {
        $this->lastQryErr = $error;
    }

    public function getError() {
        return $this->lastQryErr;
    }

    public function setIgnore() {
        $this->ignore = 'IGNORE';
    }

}
