<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use azharFactory\AzharFactory as AzharFactory;

class Security {

    public static function NcheckValues($value, $allowedTags = '') {
        // Use this function on all those values where you want to check for both sql injection and cross site scripting
        if (is_array($value)) {
            $str = array_map(array('azharFramework\Security', 'nCheckValues'), $value);
            return $str;
        }
        if (is_string($value)) {
            $value = trim($value); // Trim the value
        }

        // Suggested by Shafiq Sb to avoid Code & Script Injection
        $value = str_ireplace(array('<script>', '</script>', '%3Cscript%3E', '%3C/script%3E', '<_script>', '</_script>', '%3C_script%3E', '%3C/_script%3E'), array(''), $value);
        if ($allowedTags == '') {
            $value = str_replace(array('%3C', '%3E', '<', '>'), array(''), $value);
            // Convert all &lt;, &gt; etc. to normal html and then strip these
            $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
            $value = strip_tags($value, $allowedTags); // Strip HTML Tags
        } else {
            $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
            $value = strip_tags($value, $allowedTags); // Strip HTML Tags
        }

        if (defined('DEFAULT_DATABASE')) {
            $value = AzharFactory::get(DEFAULT_DATABASE)->realEscapeString(array("value" => $value));
        }
        $value = str_replace(array('\r\n', '\n', '\r'), "\n", $value);

        return $value;
    }

    public static function encoded($str) {
        $str = trim($str);
        $encoded = '';
        if (trim($str) != '') {
            $key = md5('R!@O#$Z%^E&*E(_P+.K');
            $encoded = str_replace('+', '::', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_CBC, md5($key))));
        }
        return $encoded;
    }

    public static function decoded($str, $callFrom = '') {
        $str = trim($str);
        $decoded = '';
        if (trim($str) != '') {
            $str = str_replace('::', '+', $str);
            $key = md5('R!@O#$Z%^E&*E(_P+.K');
            $decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_CBC, md5($key)), "\0");
        }

        return $decoded;
    }

}
