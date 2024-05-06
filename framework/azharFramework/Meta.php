<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class Meta {

    protected $oMeta;

    public function __construct($oMeta) {
        $this->oMeta = $oMeta;
    }

    public function getHead() {
        return $this->oMeta->getHead();
    }

    public function set($params) {
        return $this->oMeta->setHead($params);
    }

    public function setHead($params) {
        return $this->oMeta->setHead($params);
    }

    public function setTitle($title) {
        $this->oMeta->setTitle($title);
    }

    public function getTitle() {
        return $this->oMeta->getTitle();
    }

    public function setDesc($desc) {
        $this->oMeta->setDesc($desc);
    }

    public function getDesc() {
        return $this->oMeta->setDesc();
    }

    public function setKeywords($kw) {
        $this->oMeta->setKeywords($kw);
    }

    public function getKeywords() {
        return $this->oMeta->getKeywords();
    }
}