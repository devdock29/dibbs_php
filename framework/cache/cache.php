<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace cache;

class Cache {

    /**
     * get info from cache based on id

      nasCache

     *
     * pulls cached into basd on the provided id string
     * data will be returned in $this->data
     *
     * @param string $id ID string of the cached info
     * @return bool True or false - did it get back info?
     */
    private $data;
    private $cachePath;
    private $id;
    private $ttl;

    public function __construct($path) {
        $this->cachePath = $path;
    }

    public function getFromCache($id = "", $decode = "N") {
        if (!$id) {
            $id = $this->id;
        }
        $filename = $this->cachePath . "/" . substr($id, -2) . "/$id";
        if (!is_file($filename)) {
            return false;
        }
        $f = @fopen($filename, "r");
        $file = fread($f, filesize($filename));
        fclose($f);
        list($exp, $data) = explode("__::__", $file);
        if ($exp < time()) {
            @unlink($filename);
            return false;
        }
        if ($decode == "Y") {
            $data = base64_decode($data);
        }
        return $data;
    }

    function putInCache($id = "", $data = "", $ttl = "", $encode = "N") {
        if (!$id) {
            $id = $this->id;
            $data = $this->data;
            $ttl = $this->ttl;
        }
        $exptime = time() + $ttl;
        $filename = $this->cachePath . "/" . substr($id, -2) . "/$id";
        if ($encode == "Y") {
            $data = base64_encode($data);
        } else {
            $data = preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $data);
        }
        $output = $exptime . "__::__" . $data;
        $f = @fopen($filename, "w");
        if (!$f) {
            return false;
        }
        fputs($f, $output);
        fclose($f);
        return true;
    }

}
