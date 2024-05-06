<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace helpers;

class Analytics {

    const COUNTRIES = [
        'china' => [
            'blocked' => [
                'google',
                'facebook',
            ]
        ],
        'hong kong' => [
            'blocked' => [
                'google',
                'facebook',
            ]
        ]
    ];

    static function isAllowed($service, $country = NULL) {
        if (empty($country)) {
            $country = (!empty($_SERVER['GEOIP_COUNTRY_NAME']) ? strtolower($_SERVER['GEOIP_COUNTRY_NAME']) : '');
        }
        return !(!empty(self::COUNTRIES[$country]['blocked']) && in_array($service, self::COUNTRIES[$country]['blocked']));
    }

}