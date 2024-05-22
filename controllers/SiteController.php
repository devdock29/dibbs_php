<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
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
