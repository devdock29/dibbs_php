<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharLogger;

class azharLoggerClass {

    private static $errorLogsFilePath = "";
    private static $errorLogsMod = 0; // 0 = will echo on page, 1 = write to error logs
    private static $levelsToShow = 4;

    public static function initLogger($params) {
        if (isset($params['errorLogsMod'])) {
            self::$errorLogsMod = $params['errorLogsMod'];
        }
        if (isset($params['logFilePath'])) {
            self::$errorLogsFilePath = $params['logFilePath'];
        }
    }

    public static function backTrace($levelsToRemove = 2) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //removing backTrace function trace and logIT function trace
        for ($i = 0; $i < $levelsToRemove; $i++) {
            array_shift($trace);
        }
        $traceString = "";
        $i = 0;
        if (is_array($trace) && !empty($trace)) {
            foreach ($trace as $level) {
                $traceString .= " at class " . (isset($level['class']) ? $level['class'] : '') . " at function " . (isset($level['function']) ? $level['function'] : '') . " at Line#" . (isset($level['line']) ? $level['line'] : '') . '<br>\n';
                if (++$i > static::$levelsToShow) {
                    break;
                }
            }
        }
        return $traceString;
    }

    public static function logIt($params) {
        $errorFilePath = static::backTrace();
        if (self::$errorLogsMod) {
            $fileGone = '';
            if (isset($params['errorCode']) && $params['errorCode'] == '2006')
                $fileGone = 'gone-'; //if function true assign $fileGone new value
            $browser = get_browser(); //standard php function
            if ($browser) {
                $browserVer = $browser->browser . " " . $browser->version; //Example: Default Browser 0
                $platform = $browser->platform;
            }
            //Placeholders
            $cookies = 'NOT ASSIGNED';
            $username = 'NOT ASSIGNED';
            $serverMachine = 'NOT ASSIGNED';
            //Error String to write
            $separator = "<font face=verdana size=2><strong>Error Occured at:</strong> " . date("h:i:s") . " <strong>Browser:</strong> " . $browserVer . "<strong> Platform:</strong> " . $platform . " <strong> Cookies:</strong> " . $cookies . " <strong> Server Machine:</strong> " . (defined('SERVER_MACHINE') ? SERVER_MACHINE : '---') . "<br><strong> Error File:</strong> " . $errorFilePath . "<br><strong> Username:</strong> " . $username . "<br><strong> Error Type:</strong> " . $params['type'] . "<br><strong> Error Message:</strong> " . $params['contents'] . "</font><br><hr>";
            //$separator = "\n------------------" . date("h:i:s") . "------------------\n" . $params['type'] . "\n";
            if(!is_dir(self::$errorLogsFilePath . "" . date("Y"))) {
                mkdir(self::$errorLogsFilePath . "" . date("Y"), 0777, true);
            }
            $fp = fopen(self::$errorLogsFilePath . "" . date("Y") . "/" . date("Y-m-d") . ".html", "a");
            if ($fp) {
                fwrite($fp, $separator);
                fclose($fp);
            }
        } else {
            echo $params['contents'] . "<br />" . $errorFilePath;
        }
    }

}
