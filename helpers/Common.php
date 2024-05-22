<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace helpers;

use \azharFramework\mailer\Email;
use DateTime;
use DateInterval;
use DatePeriod;

class Common extends AppHelper {

    public static function getIP() {
        if ($_SERVER) {
            if (isset($_SERVER["REMOTE_ADDR"])) {
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

    public static function requestMethodValidation($arr, $rqstMethod) {
        $rqstMethod = strtoupper($rqstMethod);
        if (!in_array($rqstMethod, $arr)) {
            echo "Invalid REQUEST METHOD";
            exit;
            //$data['status'] = 'ERR';
            //return ResponseBuilderHelper::render($data);
        }
    }

    public static function genRandomString($length, $type) {
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

    public static function sendMail($param) {
        /*
        * emailAddress
        * subject
        * message
        */
        if (empty($param['emailFrom'])) {
            $param['emailFrom'] = FROM_EMAIL;
        }
        $param['toName'] = (isset($param['toName']) ? $param['toName'] : '');
        $param['cc'] = (isset($param['cc']) ? $param['cc'] : '');
        $param['bcc'] = (isset($param['bcc']) ? $param['bcc'] : (!empty(BCC_EMAIL) ? BCC_EMAIL : ''));
        $param['attachment'] = (isset($param['attachment']) ? $param['attachment'] : '');
        $param['replyTo'] = (isset($param['replyTo']) ? $param['replyTo'] : '');
        $param['domainBrandName'] = DOMAIN_NAME;
        $param['fromName'] = (isset($param['fromName']) ? $param['fromName'] : DOMAIN_NAME);
        //framewrork email function called
        return Email::sendMail($param, 'N');
    }

    public static function isValidInput($value, $type) {
        $value = trim($value);
        /* 	Validate Input data in the form number, alNum or alpha */
        switch ($type) {
            case "number": // For numbers only i.e 123123123123
                {
                    $return = (strlen($value) && ctype_digit($value) ? true : false);
                    break;
                }
            case "alNum": // for letters or digits only i.e, asd12asd31asd3 or asdsAS
                {
                    $return = (strlen($value) && ctype_alnum($value) ? true : false);
                    break;
                }
            case "alpha": // for alphabetic only i.e, [a-zA-Z]
                {
                    $return = (strlen($value) && ctype_alpha($value) || $this->is_arabic($value) ? true : false);
                    break;
                }
            case "email": {
                    $value = strtolower($value);
                    $return = (!preg_match("/^([a-z0-9_]|\\-|\\.|\\+)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/", $value) ? false : true);
                    break;
                }
            case "date": {
                    $ar = explode("-", $value);
                    $return = (checkdate((int) $ar[1], (int) $ar[2], (int) $ar[0]) ? true : false);
                    break;
                }
            case "name": {
                    $value = strtolower($value);
                    //$return = (!preg_match("/^[\sa-z]+$/", $value) ? false : true);
                    $return = (preg_match("/^[\sa-z]+$/", $value) || $this->is_arabic($value) ? true : false);
                    break;
                }
            case "url": {
                    $url = strtolower($value);

                    $sisterSites = array('www.sastidukan.com', 'www.earn4ever.pk', 'www.funbook-pk.com');

                    if (substr($url, 0, 4) != "http")
                        $url = 'http://' . $url;

                    $trimmedwithoutWWW = str_replace('http://www.', '', $url);

                    $basenameUrl = basename($url);
                    $trimmedwithoutWWW = basename($trimmedwithoutWWW);

                    if (in_array($basenameUrl, $sisterSites) || in_array($trimmedwithoutWWW, $sisterSites))
                        return true;

                    $cu = curl_init();
                    curl_setopt($cu, CURLOPT_URL, $url);
                    curl_setopt($cu, CURLOPT_NOBODY, 1);
                    curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 1);
                    $output = curl_exec($cu);
                    $codes = curl_getinfo($cu, CURLINFO_HTTP_CODE);
                    if ($codes == '200' || $codes == '405' || (SITE_AT == 'local' && $codes == '403'))
                        $return = true;
                    else
                        $return = false;

                    break;
                }
            default:
                $return = false;
        }
        return $return;
    }

    public static function genRandomId() {
        /* Generates 14 digit random Id based on time, IP address and pid
         * consider using mt_rand in the future
         * also srand can be dropped starting from php 4.2
         * not sure how good their seeding would be though
         */

        srand((double) microtime() * 1000000);
        $abcdef = rand(1000000, 9999999);

        $ip = getenv("REMOTE_ADDR");
        $ip = substr($ip, 0, 8);
        $ip = preg_replace("/\./", "", $ip);
        //srand($ip);
        $ghij = rand(1000, 9999);

        $pid = getmypid();
        srand($pid);
        $kl = rand(100, 999);

        $number = $abcdef . $ghij . $kl;
        return $number;
    }

    public static function encoded($str) {
        $string = trim($str);
        $key = md5('R!@O#$Z%^E&*E(_P+.K');
        $strLen = strlen($string);
        $keyLen = strlen($key);
        for ($i = 0; $i < $strLen; $i++) {
            $ordStr = ord(substr($string,$i,1));
            if ($j == $keyLen) { $j = 0; }
            $ordKey = ord(substr($key,$j,1));
            $j++;
            $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
        }
        return $hash;
        
        /*$encoded = '';
        if (trim($str) != '') {
            $key = md5('R!@O#$Z%^E&*E(_P+.K');
            //$encoded = str_replace('+', '::', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_CBC, md5($key))));
            $encoded = mcrypt_ecb (MCRYPT_3DES, $key, $str, MCRYPT_ENCRYPT);
        }
        return $encoded;*/
    }

    public static function decoded($str, $callFrom = '') {
        $string = trim($str);
        $key = md5('R!@O#$Z%^E&*E(_P+.K');
        $strLen = strlen($string);
        $keyLen = strlen($key);
        for ($i = 0; $i < $strLen; $i+=2) {
            $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
            if ($j == $keyLen) { $j = 0; }
            $ordKey = ord(substr($key,$j,1));
            $j++;
            $hash .= chr($ordStr - $ordKey);
        }
        return $hash;
        
        /*$decoded = '';
        if (trim($str) != '') {
            $str = str_replace('::', '+', $str);
            $key = md5('R!@O#$Z%^E&*E(_P+.K');
            //$decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($str), MCRYPT_MODE_CBC, md5($key)), "\0");
            $decoded = mcrypt_ecb (MCRYPT_3DES, $key, $str, MCRYPT_DECRYPT);
        }*/
        return $decoded;
    }

    public static function uploadImage($files, $file_name) {
        $allowed = array('jpg', 'jpeg', 'gif', 'png', 'svg');
        $dir = "uploads/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        if (!empty($files[$file_name]['tmp_name'])) {
            $ext = pathinfo($files[$file_name]['name'], PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                return false;
            }
            $randId = \helpers\Common::genRandomId();
            $bucket = \helpers\Common::genRandomString(2, 'INT');
            if (!file_exists($dir . $bucket . "/")) {
                mkdir($dir . $bucket . "/", 0777);
            }
            $imgFileName = time() . '_' . $randId;
            $fullPath = $dir . $bucket . "/" . $imgFileName . "." . $ext;
            //if (move_uploaded_file($files[$file_name]['tmp_name'], getcwd() . $fullPath)) {
            if (move_uploaded_file($files[$file_name]['tmp_name'], $fullPath)) {
                return $fullPath;
            } else {
                return false;
            }
        }
        return false;
    }
    
    public static function checkPayment($arrData) {
        $curl = curl_init();
        $headers = array(
            'Authorization: Basic c2tfdGVzdF81MUp4bU1yQXZuMk5UYjFkWmNyUkZlMDZvd0dLaGpzU0JyNGE0NklKaTNVTFQ1WDd0WDdYQU1CUEFCZGlsRERRbjVlUERLUXpTRFAyRDAyQ0hVZUNxeEkxZDAwSkQ3UmphVXc6',
            'Content-Type: application/x-www-form-urlencoded'
        );
        $url = 'https://api.stripe.com/v1/payment_intents';
        $postFields = 'amount='.$arrData['amount'].'&currency=usd&payment_method_types%5B%5D=card&payment_method='.$arrData['payment_method'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $ret = (array) json_decode($response);
        $oStripeLogsModel = new \models\StripeLogsModel();
        $logParams['order_id'] = $arrData['order_id'];
        $logParams['type'] = 'checkPayment';
        $logParams['endpoint'] = $url;
        $logParams['headers'] = print_r($headers, true);
        $logParams['payload'] = $postFields;
        $logParams['response'] = print_r($ret, true);
        $oStripeLogsModel->addLog($logParams);
        return $ret;
    }
    
    public static function confirmPayment($arrData) {
        $curl = curl_init();
        $headers = array(
            'Authorization: Basic c2tfdGVzdF81MUp4bU1yQXZuMk5UYjFkWmNyUkZlMDZvd0dLaGpzU0JyNGE0NklKaTNVTFQ1WDd0WDdYQU1CUEFCZGlsRERRbjVlUERLUXpTRFAyRDAyQ0hVZUNxeEkxZDAwSkQ3UmphVXc6',
            'Content-Type: application/x-www-form-urlencoded'
        );
        $url = 'https://api.stripe.com/v1/payment_intents/'.$arrData['payment_id'].'/confirm';
        $postFields = 'payment_method='.$arrData['payment_method'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $ret = (array) json_decode($response);
        $oStripeLogsModel = new \models\StripeLogsModel();
        $logParams['order_id'] = $arrData['order_id'];
        $logParams['type'] = 'confirmPayment';
        $logParams['endpoint'] = $url;
        $logParams['headers'] = print_r($headers, true);
        $logParams['payload'] = $postFields;
        $logParams['response'] = print_r($ret, true);
        $oStripeLogsModel->addLog($logParams);
        return $ret;
    }

}
