<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\csrf;

class Token {

    public static function generate() {
        $token = base64_encode(openssl_random_pseudo_bytes(32));
        $token = str_replace(array('+', '/', '=', ' '), '', $token);
        return $token;
    }

    public static function check($token, $sToken) {
        return $token === $sToken;
    }
}
