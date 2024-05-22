<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

class AzharHelper {

    use UtilityTrait;

    public static function encodeIntShortcut($integer, $baseCount = '62') {
        $integer = $integer + 100000000;
        $ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ALLOWED_CHARS36 = '0123456789abcdefghijklmnopqrstuvwxyz';
        $ALLOWED_CHARS36CAP = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($baseCount == '36') {
            $base = $ALLOWED_CHARS36;
            $length = 36;
        } elseif ($baseCount == '36CAP') {
            $base = $ALLOWED_CHARS36CAP;
            $length = 36;
        } else {
            $base = $ALLOWED_CHARS;
            $length = 62;
        }
        $out = "";
        while ($integer > ($length - 1)) {
            $out = $base[(int) fmod($integer, $length)] . $out;
            $integer = floor($integer / $length);
        }
        return $base[intval($integer)] . $out;
    }

    public static function decodeIntShortcut($string, $baseCount = '62') {
        $ALLOWED_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ALLOWED_CHARS36 = '0123456789abcdefghijklmnopqrstuvwxyz';
        $ALLOWED_CHARS36CAP = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($baseCount == '36') {
            $base = $ALLOWED_CHARS36;
            $length = 36;
        } elseif ($baseCount == '36CAP') {
            $base = $ALLOWED_CHARS36CAP;
            $length = 36;
        } else {
            $base = $ALLOWED_CHARS;
            $length = 62;
        }

        $size = strlen($string) - 1;
        $string = str_split($string);
        $out = strpos($base, array_pop($string));
        foreach ($string as $i => $char) {
            $out += strpos($base, $char) * pow($length, $size - $i);
        }
        return $out - 100000000;
    }

    public static function encoded($str) {
        $str = trim($str);
        $encoded = '';
        if (trim($str) != '') {
            $key = md5((AzharConfigs::getNthKey("encoding", "salt") ? AzharConfigs::getNthKey("encoding", "salt") : 'I#AM%!FEELING*+$LUCKY'));
            $encoded = str_replace('+', '::', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_CBC, md5($key))));
        }
        return $encoded;
    }

    public static function decoded($str) {
        $str = trim($str);
        $decoded = '';
        if (trim($str) != '') {
            $str = str_replace('::', '+', $str);
            $key = md5((AzharConfigs::getNthKey("encoding", "salt") ? AzharConfigs::getNthKey("encoding", "salt") : 'I#AM%!FEELING*+$LUCKY'));
            $decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_CBC, md5($key)), "\0");
        }

        return $decoded;
    }

    public static function getIP() {
        if ($_SERVER) {
            if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && !empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $realip = $_SERVER["HTTP_CF_CONNECTING_IP"];
            } elseif (isset($_SERVER['GEOIP_ADDR']) && !empty($_SERVER['GEOIP_ADDR'])) {
                $realip = $_SERVER["GEOIP_ADDR"];
            } elseif (isset($_SERVER["REMOTE_ADDR"])) {
                $realip = $_SERVER["REMOTE_ADDR"];
            } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            }
        } else {
            if (getenv('REMOTE_ADDR')) {
                $realip = getenv('REMOTE_ADDR');
            } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            }
        }
        if (function_exists('filter_var')) {
            if (filter_var($realip, FILTER_VALIDATE_IP))
                return $realip;
            else
                return false;
        } else
            return $realip;
    }

    public static function is_arabic($str, $percen = 0.6) {
        if (mb_detect_encoding($str) !== 'UTF-8') {
            $str = mb_convert_encoding($str, mb_detect_encoding($str), 'UTF-8');
        }
        $matches = [];
        preg_match_all('/.|\n/u', $str, $matches);
        $chars = $matches[0];
        $arabic_count = 0;
        $latin_count = 0;
        $total_count = 0;
        foreach ($chars as $char) {
            $pos = self::uniord($char);

            if ($pos >= 1536 && $pos <= 1791) {
                $arabic_count++;
            } else if ($pos > 123 && $pos < 123) {
                $latin_count++;
            }
            $total_count++;
        }

        if ($arabic_count > 0 && ($arabic_count / $total_count) > $percen) {
            return true;
        }
        return false;
    }

    public static function getNthKey($aritterator) {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        for ($i = 1; $i < $numargs; $i++) {
            if (isset($aritterator[$arg_list[$i]]) || array_key_exists($arg_list[$i], $aritterator)) {
                $aritterator = $aritterator[$arg_list[$i]];
            } else {
                return false;
            }
        }
        return $aritterator;
    }

}
