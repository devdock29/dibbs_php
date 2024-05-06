<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM\MySQL;

class UnionQueryBuilder {

    private $oQueryBuilder = array();

    public function add(QueryBuilder $obj) {
        $this->oQueryBuilder[] = $obj;
    }

    public function build(QueryBuilder $obj) {
        $this->add($obj);
        $dataTypes = '';
        $params = array();
        $finalQuery = '';
        foreach ($this->oQueryBuilder as $obj) {
            $obj->hasUnion = null;
            $obj->build();
            $finalQuery .= $obj->getQuery() . " UNION ";
            $temp = $obj->getQueryParams();
            $dataTypes .= array_shift($temp);
            $params = array_merge($params, $temp);
        }
        array_unshift($params, $dataTypes);
        return array(trim($finalQuery, " UNION "), $params);
    }

}
