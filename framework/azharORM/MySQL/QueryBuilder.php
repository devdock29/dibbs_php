<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM\MySQL;

class QueryBuilder {

    private $tableName;
    private $fields;
    public $conditions = array();
    private $order = array();
    private $unionOrder;
    private $group = "";
    private $having = "";
    private $limit;
    private $limitUnion;
    private $query;
    private $queryParams;
    private $oMyDBClass;
    private $start;
    private $offset;
    private $countField;
    private $setFields = array();
    private $setVals = array();
    private $setValsDataType;
    public $hasUnion;
    private $oUnionQueryBuilder;

    public function getQuery() {
        return $this->query;
    }

    public function getQueryParams() {
        return $this->queryParams;
    }

    public function __construct($oMyDBClass) {
        $this->oMyDBClass = $oMyDBClass;
    }

    public function getUnionQueryBuilder() {
        if ($this->oUnionQueryBuilder == null) {
            $this->oUnionQueryBuilder = new UnionQueryBuilder();
        }
        return $this->oUnionQueryBuilder;
    }

    public function union() {
        $this->getUnionQueryBuilder()->add(clone $this);
        $oSelf = new QueryBuilder($this->oMyDBClass);
        foreach ($oSelf as $key => $value) {
            if ($key != 'oUnionQueryBuilder') {
                $this->$key = $oSelf->$key;
            }
        }
        unset($oSelf);
        $this->hasUnion = true;
        return $this;
    }

    public function select($fromFields = "*") {
        $this->fields = $fromFields;
        return $this;
    }

    public function from($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

    public function whereLike($dataType, $field, $value, $condOperator = "LIKE") {
        $this->conditions[] = array(
            "condType" => "",
            "operator" => "AND",
            "dataType" => $dataType,
            "condition" => $field . " " . $condOperator . " " . '?',
            "value" => $value
        );
        return $this;
    }

    public function whereOrLike($dataType, $field, $value, $condOperator = "LIKE") {
        $this->conditions[] = array(
            "condType" => "",
            "operator" => "OR",
            "dataType" => $dataType,
            "condition" => $field . " " . $condOperator . " " . '?',
            "value" => $value
        );
        return $this;
    }

    public function whereNull($field, $operator = "AND") {
        $this->conditions[] = array(
            "operator" => $operator,
            "condition" => $field . ' IS NULL '
        );
        return $this;
    }

    public function whereNotNull($field, $operator = "AND") {
        $this->conditions[] = array(
            "operator" => $operator,
            "condition" => $field . ' IS NOT NULL '
        );
        return $this;
    }

    public function whereOrNull($field) {
        return $this->whereNull($field, "OR");
    }

    public function whereOrNotNull($field) {
        return $this->whereNotNull($field, "OR");
    }

    public function where($dataType, $field, $value, $condOperator = "=") {
        $this->conditions[] = array(
            "condType" => "",
            "operator" => "AND",
            "dataType" => $dataType,
            "condition" => $field . " " . $condOperator . " " . '?',
            "value" => $value
        );
        return $this;
    }

    public function whereOr($dataType, $field, $value, $condOperator = "=") {
        $this->conditions[] = array(
            "condType" => "",
            "operator" => "OR",
            "dataType" => $dataType,
            "condition" => $field . ' ' . $condOperator . '?',
            "value" => $value
        );
        return $this;
    }

    public function whereIn($dataType, $field, $inArr, $condOperator = "IN") {
        $out = $this->buildIn($field, count($inArr), $dataType, $condOperator);
        $this->conditions[] = array(
            "condType" => $condOperator,
            "operator" => "AND",
            "dataType" => $out['dataType'],
            "condition" => $out['condition'],
            "value" => $inArr
        );
        return $this;
    }

    public function whereNotIn($dataType, $field, $inArr) {
        return $this->whereIn($dataType, $field, $inArr, "NOT IN");
    }

    public function whereOrIn($dataType, $field, $inArr) {
        $out = $this->buildIn($field, count($inArr), $dataType);
        $this->conditions[] = array(
            "condType" => "IN",
            "operator" => "OR",
            "dataType" => $out['dataType'],
            "condition" => $out['condition'],
            "value" => $inArr
        );
        return $this;
    }

    public function buildIn($field, $totVals, $dataType, $condOperator = "IN") {
        $type = $inVals = "";
        for ($i = 0; $i < $totVals; $i++) {
            $inVals .= "?,";
            $type .= $dataType;
        }
        $inVals = substr($inVals, 0, -1);
        return array("condition" => '' . $field . ' ' . $condOperator . ' (' . $inVals . ')', "dataType" => $type);
    }

    public function orderBy($field, $order = "") {
        $this->order[] = $field . " " . $order;
        return $this;
    }

    public function orderByUnion($field, $order = "") {
        $this->unionOrder = $field . " " . $order;
        return $this;
    }

    public function limit($start, $offset = null) {
        $this->start = $start;
        $this->offset = $offset;
        $this->limit = "LIMIT " . $start . ($offset !== null ? ',' . $offset : '');
        if ($this->query != null) {
            $this->query .= ' ' . $this->limit;
        }
    }

    public function limitUnion($start, $offset = null) {
        $this->start = $start;
        $this->offset = $offset;
        $this->limitUnion = "LIMIT " . $start . ($offset !== null ? ',' . $offset : '');
    }

    public function groupBy($field) {
        $this->group = ' GROUP BY ' . $field . ' ';
        return $this;
    }

    public function having($field, $value, $operator = '=') {
        $this->having = ' HAVING ' . $field . $operator . $value;
    }

    public function join($field1, $field2, $condOperator = "=") {
        $this->conditions[] = array(
            "condType" => "",
            "operator" => "AND",
            "condition" => $field1 . $condOperator . $field2
        );
        return $this;
    }

    public function set($field, $value, $type) {
        $this->setFields[] = $field;
        $this->setVals[] = $value;
        $this->setValsDataType .= $type;
        return $this;
    }

    public function update() {
        $this->buildUpdate();
        $arr = array('qry' => $this->query, "whereParams" => $this->queryParams);
        return $arr;
//return $this->oMyDBClass->update($arr);
    }

    public function buildSet() {
        $set = " SET ";
        foreach ($this->setFields as $field) {
            $set .= $field . '=?,';
        }
        return rtrim($set, ",");
    }

    public function buildUpdate() {

        if ($this->setVals != '') {
            $qry = 'UPDATE ' . $this->tableName . $this->buildSet() . ' WHERE 1';
        }

        $this->query = $qry . ' ' . $this->buildWhere() . ' ';
    }

    public function buildWhere() {
        $whereCondition = $dataType = "";
        $queryParams = array();
        if ($this->setValsDataType !== null) {
            $dataType = $this->setValsDataType;
            $queryParams = $this->setVals;
        }
        foreach ($this->conditions as $condition) {
            $whereCondition .= ' ' . $condition['operator'] . ' ' . $condition['condition'];
            if (isset($condition['dataType'])) {
                $dataType .= $condition['dataType'];

                if ($condition['condType'] == "IN" || $condition['condType'] == "NOT IN") {
                    $queryParams = array_merge($queryParams, $condition['value']);
                } else {
                    $queryParams[] = $condition['value'];
                }
            }
        }
        if (!empty($queryParams) && $dataType != "") {
            array_unshift($queryParams, $dataType);
        }
        $this->queryParams = $queryParams;
        return $whereCondition;
    }

    public function query() {
        if ($this->query === null) {
            $this->build();
        }
        return $this->query;
    }

    public function count($countField) {
        $this->countField = " count(" . $countField . ") ";
        return $this->executeCount();
    }

    public function countUnion() {
        return $this->oMyDBClass->count(array(
                    "qry" => "SELECT COUNT(*) FROM(" . $this->query() . ") AS C",
                    "whereParams" => $this->queryParams));
    }

    private function executeCount() {
        $this->build($this->countField);
        return $this->oMyDBClass->count(array(
                    "qry" => $this->query,
                    "whereParams" => $this->queryParams));
    }

    public function execute() {
        $this->build();
        if ($this->start == 1 && $this->offset === null) {
            return $this->oMyDBClass->find(array(
                        "qry" => $this->query,
                        "whereParams" => $this->queryParams));
        } else {
            return $this->oMyDBClass->findAll(array(
                        "qry" => $this->query,
                        "whereParams" => $this->queryParams
            ));
        }
    }

    private function buildUnion() {
        if ($this->query === null) {
            list($this->query, $this->queryParams) = $this->getUnionQueryBuilder()->build(clone $this);
            $this->buildUnionOrderBy();
            if ($this->limitUnion != null) {
                $this->query .= ' ' . $this->limitUnion;
            }
        }
    }

    public function build($fields = "") {
        if ($this->hasUnion !== null) {
            $this->buildUnion();
            return true;
        }
        $whereCondition = $dataType = "";
        $queryParams = array();
        $qry = 'SELECT ' . ($fields == "" ? $this->fields : $fields) . ' FROM ' . $this->tableName . ' WHERE 1';
        foreach ($this->conditions as $condition) {
            $whereCondition .= ' ' . $condition['operator'] . ' ' . $condition['condition'];
            if (isset($condition['dataType'])) {
                $dataType .= $condition['dataType'];

                if ($condition['condType'] == "IN" || $condition['condType'] == "NOT IN") {
                    $queryParams = array_merge($queryParams, $condition['value']);
                } elseif (isset($condition['value'])) {
                    $queryParams[] = $condition['value'];
                }
            }
        }
        if (!empty($queryParams) && $dataType != "") {
            array_unshift($queryParams, $dataType);
        }
        $this->query = $qry . ' ' .
                $whereCondition . ' ';
        if ($fields == "") {
            $this->query = $this->query . $this->group . $this->having . ' ' . $this->buildOrderBy() . ' ' . $this->limit;
        }
        $this->queryParams = $queryParams;
    }

    public function nest($field, QueryBuilder $oQueryBuilder, $operator = "IN") {
//TODO
    }

    private function buildOrderBy() {
        if (count($this->order) > 0) {
            $order = " ORDER BY ";
            foreach ($this->order as $value) {
                $order .= $value . ",";
            }
            $order = substr($order, 0, -1);
            return $order;
        }
    }

    private function buildUnionOrderBy() {
        if ($this->unionOrder !== null) {
            $this->query = 'SELECT * FROM (' . $this->query . ')a ORDER BY ' . $this->unionOrder;
        }
    }

}
