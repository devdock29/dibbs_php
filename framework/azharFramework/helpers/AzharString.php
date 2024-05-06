<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\helpers;

class AzharString {

    public static function startsWith($haystack, $needle) {
        return strripos($haystack, $needle, -strlen($haystack));
    }

    public static function br2nl($str) {
        if ($str != '') {
            // Replace with one CRLF;
            $replace = array('<br>', '<br/>', '<br />', '<hr>', '<hr/>', '<hr />');
            $str = str_ireplace($replace, PHP_EOL, $str);
            // Replace with two CRLF
            $replace = array('</p>', '</li>', '</ul>', '</ol>', '<p>', '<ul>', '<ol>');
            $str = strip_tags(str_ireplace($replace, PHP_EOL . PHP_EOL, $str));
            // remove multiple CRLF to single CRLF
            $str = str_replace(PHP_EOL . PHP_EOL . PHP_EOL, PHP_EOL, $str);
            // reomove &nbsp;
            $str = str_replace('&nbsp;', ' ', $str);
            return trim($str);
        }
    }

    public static function cleanNonPrintableCharsR($str, $repWith = '', $perc = '0.6') {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = self::cleanNonPrintableCharsR($value);
            }
        } else {

            $str = str_replace("\n", "<NEWLINE>", $str);
            $str = preg_replace('/[^[:print:]]/', $repWith, $str);
            $str = str_replace("<NEWLINE>", "\n", $str);
            
        }
        return $str;
    }

    public static function cleanNonPrintableChars($str, $repWith = '', $perc = '0.6') {

        $str = str_replace("\n", "<NEWLINE>", $str);
        $str = preg_replace('/[^[:print:]]/', $repWith, $str);
        $str = str_replace("<NEWLINE>", "\n", $str);
        
        return $str;
    }

    public static function uniord($u) {
        // i just copied this function fron the php.net comments, but it should work fine!
        $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }

    public static function isArabic($str, $percen = '0.6') {
        //global $isArabicCallingLine;
        if (mb_detect_encoding($str) !== 'UTF-8') {
            $str = mb_convert_encoding($str, mb_detect_encoding($str), 'UTF-8');
        }

        preg_match_all('/.|\n/u', $str, $matches);
        $chars = $matches[0];
        $arabic_count = 0;
        $latin_count = 0;
        $total_count = 0;
        foreach ($chars as $char) {
            //$pos = ord($char); we cant use that, its not binary safe 
            $pos = self::uniord($char);
            //echo $char ." --> ".$pos.PHP_EOL;

            if ($pos >= 1536 && $pos <= 1791) {
                $arabic_count++;
            } else if ($pos > 123 && $pos < 123) {
                $latin_count++;
            }
            $total_count++;
        }

        //echo "<!-- Arabic Count: $arabic_count === Total Count: $total_count -->";
        if ($arabic_count > 0 && ($arabic_count / $total_count) > $percen) {
            // 60% arabic chars, its probably arabic
            return true;
        }
        return false;
    }

    public static function getRandomString($length, $type) {
        if ($length > 0) {
            switch ($type) {
                case "INT":
                    $string = "1234567890";
                    $strLen = 10;
                    break;
                case "STR":
                    $string = "abcdefghijklmnopqrstuvwxyz";
                    $strLen = 26;
                    break;
                case "MIX":
                    $string = "abcdefghijklmnpqrstuvwxyz123456789";
                    $strLen = 34;
                    break;
                case "unique":
                    return md5(uniqid(mt_rand(), true));
                    break;
            }

            $rand_id = "";
            for ($i = 1; $i <= $length; $i++) {
                $num = mt_rand(1, $strLen);
                $rand_id .= substr($string, $num - 1, 1);
            }
        }
        return strtoupper($rand_id);
    }

    public static function isProbKeywordsExist($str, $filterProbKW = "Y") {
        //echo $str."<br>";
        $probKw = array("C++", "C#", "J++", "J#");
        for ($i = 0; $i < 4; $i++) {
            if (stristr($str, $probKw[$i])) {
                if ($filterProbKW == "Y")
                    return self::filterProbKeywords($str);
                else
                    return true;
            }
        }
        return false;
    }

//End filterProbKeywords

    public static function filterProbKeywords($str, $direction = "") {
        //exit("$str, $direction");
        $text = $str;
        $probKw = array("C++", "C#", "J++", "J#");
        $repByKw = array("CPLUSPLUS", "CCHANNEL", "JPLUSPLUS", "JAVACHANNEL");
        for ($i = 0; $i < 4; $i++) {
            if ($direction == "")
                $text = str_ireplace($probKw[$i], $repByKw[$i], $text);
            else
                $text = str_ireplace($repByKw[$i], $probKw[$i], $text);
        }
        //echo "$str, $direction"; exit;
        return $text;
    }

//End filterProbKeywords

    public static function highlight($str, $str2highlight) {
        if ($str != '' && $str2highlight != '') {
            $str2highlight = str_replace(array('"'), '', rawurldecode($str2highlight));
            //$str2highlight = $this->cleanNonPrintableChars($str2highlight);
            $str2highlight = str_replace(array('"', "'"), '', $str2highlight);
            //echo "</h1>#($str2highlight)#is</h1>";
            //$text = preg_replace("#($str2highlight)#/is", "<span class'hl'>" . $str2highlight ."</span>", $str);
            $text = str_ireplace($str2highlight, "<em>" . $str2highlight . "</em>", $str);

            if ($text == '')
                $text = $str;
            //echo $str2highlight . "<span class'hl'>" . $str2highlight ."</span>";
        } else
            $text = $str;

        return $text;
    }

//End highlight	
}
