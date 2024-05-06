<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace controllers;

class SiteController extends AppController {
    
    public function errorAction() {
        $data = array();
        $metaData['title'] = 'Not Found on '.DOMAIN_BRAND_NAME;
        $this->meta()->set($metaData);
        
        $this->renderT('error', $data);
    }
    
}
