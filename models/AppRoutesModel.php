<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace models;

use \azharFactory\AzharFactory as AzharFactory;
use \AzharConstants;

class AppRoutesModel extends AppModel implements \azharFramework\interfaces\AppRoutes {

    public function route($key) {
        if ($key == '' || $key === null) {
            return false;
        }
        
        $data = $this->find(array('fields' => 'id, url, route, title, keywords, description, type, typeId', 'whereClause' => 'url=? AND active=?', 'whereParams' => ['ss', $key, 'Y']));
        if ($data !== null) {
            if (isset($data['route']) && $data['route'] != '') {
                $Route = explode('@', $data['route']);
                $json = end($Route);
                $array = json_decode($json, true);
                $array['routeID'] = $data['id'];
                $json = json_encode($array);
                array_pop($Route);
                array_push($Route, $json);
                AzharFactory::add(AzharConstants::GET, array($data['typeId']));
                return implode('@', $Route);
            } else {
                $this->delete(array('whereClause' => ' id = ? LIMIT 1 ', 'whereParams' => ['i', $data['id']]));
            }
        }
        return false;
    }

}
