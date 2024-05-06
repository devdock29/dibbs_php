<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\data;

use \helpers\Common;

class Validate {

    private $data;
    private $error = array('status' => true, 'response' => '');

    public function input($_value, $type) {
        $value = trim($_value);
        switch ($type) {
            case "empty": {
                    $return = ($value != '' ? true : false);
                    break;
                }
            case "number": {
                    if (strlen($value) != 0) {
                        $return = (ctype_digit($value) ? true : false);
                    } else
                        $return = true;
                    break;
                }
            case "alNum": {
                    if (strlen($value) != 0) {
                        $return = (ctype_alnum($value) ? true : false);
                    } else
                        $return = true;
                    break;
                }
            case "alpha": {
                    if (strlen($value) != 0) {
                        $return = (ctype_alpha($value) || Common::is_arabic($value) ? true : false);
                    } else
                        $return = true;
                    break;
                }
            case "email": {
                    if (strlen($value) != 0) {
                        $value = strtolower($value);
                        $return = (!preg_match("/^([a-z0-9_]|\\-|\\.|\\+)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/", $value) ? false : true);
                    } else
                        $return = true;
                    break;
                }
            case "date": {
                    if (strlen($value) != 0) {
                        $ar = explode("-", $value);
                        $return = (checkdate((int) $ar[1], (int) $ar[2], (int) $ar[0]) ? true : false);
                    } else
                        $return = true;
                    break;
                }
            case "name": {
                    if (strlen($value) != 0) {
                        $value = strtolower($value);
                        $return = (preg_match("/^[\sa-z]+$/", $value) || is_arabic($value) ? true : false);
                    } else
                        $return = true;
                    break;
                }
            case "url": {

                    $url = strtolower($value);


                    if (substr($url, 0, 4) != "http")
                        $url = 'http://' . $url;

                    $trimmedwithoutWWW = str_replace('http://www.', '', $url);

                    $basenameUrl = basename($url);
                    $trimmedwithoutWWW = basename($trimmedwithoutWWW);


                    $cu = curl_init();
                    curl_setopt($cu, CURLOPT_URL, $url);
                    curl_setopt($cu, CURLOPT_NOBODY, 1);
                    curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 1);
                    $output = curl_exec($cu);
                    $codes = curl_getinfo($cu, CURLINFO_HTTP_CODE);
                    if ($codes == '200' || $codes == '405' || (SITE_AT == 'sailfish' && $codes == '403'))
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

    public function __set($column, $value = array()) {
        $this->data[$column] = $value;
    }

    public function __get($column) {
        return $this->data[$column];
    }

    public function setError($key, $errorMessage) {
        $this->error['status'] = false;
        $this->error['response'][$key] = $errorMessage;
    }

    public function getError() {
        return $this->error;
    }

    public function checkCondition($array, $key, $inputValue, $rule = null, $errorMessage = null) {
        foreach ($array as $rule => $errorMessage) {
            $condition = $this->input($inputValue, $rule);
            if (!$condition) {
                $this->setError($key, $errorMessage);
                break;
            }
        }
    }

    public function all($_data = array()) {
        $formData = $_data;
        $status = true;
        $response = null;
        foreach ($formData as $key => $value) {
            $condition = $this->input($value, 'empty');
            if (!$condition) {
                $this->setError($key, 'Empty Input');
            }
        }
        $getError = $this->getError();
        return $getError['status'];
    }

    public function verify() {
        $formData = $this->data;
        $status = true;
        $response = null;
        foreach ($formData as $key => $value) {
            $inputValue = array_shift($formData[$key]); // Stores values from form (in view)
            $this->checkCondition($formData[$key], $key, $inputValue);
        }
        $getError = $this->getError();
        return $getError['status'];
    }

    public function obligate($params, $rules) {
        foreach ($params as $key => $value) {
            if (array_key_exists($key, $rules)) {
                $this->checkCondition($rules[$key], $key, $value);
            }
        }

        $getError = $this->getError();
        return $getError['status'];
    }

}
