<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\interfaces;

interface MetaInterface {
    //put your code here
    public function setHead($params);

    public function getHead();

    public function setTitle($title);

    public function getTitle();

    public function setDesc($desc);

    public function getDesc();

    public function setKeywords($kw);

    public function getKeywords();
}
