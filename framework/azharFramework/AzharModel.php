<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class AzharModel {

    protected $relation;
    protected $pk;
    protected $pkType = 'i';
    protected $connectionName = DEFAULT_DATABASE;

    use UtilityTrait;

    use traits\Model;

    public function getMyInstance($id = null) {
        return $this->db($this->connectionName)->newTable($this->relation, $id);
    }

    public function countByPK() {
        return $this->db()->count(array('qry' => 'SELECT COUNT(' . $this->pk . ') FROM ' . $this->relation));
    }

    public function count($arr) {
        $whereClause = $arr['whereClause'];
        $whereParams = $arr['whereParams'];
        $data = $this->db($this->connectionName)->count(array("qry" => 'SELECT COUNT(' . $this->pk . ') FROM ' . $this->relation . ' WHERE ' . $whereClause, 'whereParams' => $whereParams));
        return $data;
    }

    public function findByPK($value, $fields = "*") {
        return $this->db($this->connectionName)->find(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $this->pk . " = ?", "whereParams" => array($this->pkType, $value)));
    }

    public function findByFieldInt($field, $value, $fields = "*") {
        return $this->db($this->connectionName)->find(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $field . " = ?", "whereParams" => array('i', $value)));
    }

    public function findAllByFieldInt($field, $value, $fields = "*") {
        return $this->db($this->connectionName)->findAll(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $field . " = ?", "whereParams" => array('i', $value)));
    }

    public function findByFieldString($field, $value, $fields = "*") {
        return $this->db($this->connectionName)->find(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $field . " = ?", "whereParams" => array('s', $value)));
    }

    public function deleteByPK($value) {
        return $this->db($this->connectionName)->delete(array("qry" => "DELETE FROM " . $this->relation . " WHERE " . $this->pk . " = ?", "whereParams" => array($this->pkType, $value)));
    }

    public function find($arr) {
        $fields = $arr['fields'];
        $whereClause = $arr['whereClause'];
        $whereParams = $arr['whereParams'];
        $singleRecord = $this->db($this->connectionName)->find(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $whereClause, "whereParams" => $whereParams));
        return $singleRecord;
    }

    public function findAll($arr) {
        $fields = $arr['fields'];
        $whereClause = $arr['whereClause'];
        $whereParams = $arr['whereParams'];
        $allRecords = $this->db($this->connectionName)->findAll(array("qry" => "SELECT " . $fields . " FROM " . $this->relation . " WHERE " . $whereClause, "whereParams" => $whereParams));
        return $allRecords;
    }

    public function insert($param, $updateKey = false) {
        if ($param != null) {
            if ($updateKey === false || $updateKey === '') {
                $obj = $this->db($this->connectionName)->newTable($this->relation);
            } else {
                $obj = $this->db($this->connectionName)->newTable($this->relation, $updateKey);
            }
            if (isset($param['insertIgnore'])) {
                $obj->setIgnore();
                unset($param['insertIgnore']);
            }
            foreach ($param as $key => $value) {
                $obj->$key = $value;
            }
            $obj->save();
            if ($obj->isSaved()) {
                return $obj->getPK();
            } else {
                return $obj->getError();
            }
            return $obj->isSaved();
        }
        return false;
    }

    public function delete($arr) {
        $whereClause = $arr['whereClause'];
        $whereParams = $arr['whereParams'];
        return $this->db()->delete(array('qry' => 'DELETE FROM ' . $this->relation . ' WHERE ' . $whereClause, 'whereParams' => $whereParams));
    }

    public function update($arr) {
        $fields = $arr['fields'];
        $whereClause = $arr['whereClause'];
        $whereParams = $arr['whereParams'];
        return $this->db()->update(array("qry" => "UPDATE " . $this->relation . " SET " . $fields . " WHERE " . $whereClause, "whereParams" => $whereParams));
    }

}
