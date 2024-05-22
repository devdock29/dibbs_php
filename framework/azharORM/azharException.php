<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharORM;

class azharException extends \Exception {
    /* Implementing Method overloading for getStackTrace method */

    public function __call($name, $arguments) {
        if ($name === 'getStackTrace') {
            return $this->getStackTrace2();
        }
    }

    public static function __callStatic($name, $arguments) {
        if ($name === 'getStackTrace') {
            if (count($arguments) > 0) {
                return self::getStackTrace1($arguments[0]);
            }
        }
    }

    private static function getStackTrace1(\Exception $oException) {
        $trace = $oException->getTrace();

        $result = 'Exception: "';
        $result .= $oException->getMessage();
        $result .= '" @ ';
        if ($trace[0]['class'] != '') {
            $result .= $trace[0]['class'];
            $result .= '->';
        }
        $result .= $trace[0]['function'];
        $result .= '();<br />';

        return $result;
    }

    private function getStackTrace2() {
        $trace = $this->getTrace();

        $result = 'Exception: "';
        $result .= $this->getMessage();
        $result .= '" @ ';
        if ($trace[0]['class'] != '') {
            $result .= $trace[0]['class'];
            $result .= '->';
        }
        $result .= $trace[0]['function'];
        $result .= '();<br />';

        return $result;
    }

}
