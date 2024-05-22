<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace cache;

use \azharFramework\AzharConfigs;

class AzharCache {

    private $oCache;

    private function getCacheObject() {
        if ($this->oCache === null) {
            $this->oCache = new Cache(AzharConfigs::getNthKey('cache', 'path'));
        }
        return $this->oCache;
    }

    public function get($id, $decode = "N") {
        if (AzharConfigs::getNthKey('cache', 'rzCache') &&
                AzharConfigs::getNthKey('cache', 'enable') &&
                AzharConfigs::getNthKey('cache', 'path')) {
            $data = trim($this->getCacheObject()->getFromCache($id, $decode));
            if ($data != null) {
                $data = ($decode == "Y" ? unserialize($data) : $data);
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function put($id, $data, $ttl = 60, $encode = "N") {
        $data = ($encode == "Y" ? serialize($data) : $data);
        return $this->getCacheObject()->putInCache($id, $data, $ttl, $encode);
    }

    public function remove($id) {
        if (AzharConfigs::getNthKey('cache', 'path')) {
            $filename = AzharConfigs::getNthKey('cache', 'path') . "/" . substr($id, -2) . "/$id";
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

}
