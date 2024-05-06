<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework;

use \azharLogger\azharLoggerClass;

class SQLMonitor {

    private static $data;
    private static $startTime;
    private static $statementStartTime;
    private static $statementEndTime;
    private static $endTime;
    private static $totalTime = 0;

    public static function startTimer() {
        static::$startTime = static::microtime();
    }

    public static function stopTimer($qry, $where) {
        static::$endTime = static::microtime();
        static::add($qry, $where);
    }

    public static function startStatementTimer() {
        static::$statementStartTime = static::microtime();
    }

    public static function stopStatementTimer() {
        static::$statementEndTime = static::microtime();
    }

    private static function buildQueryTime() {
        $statementTime = static::$statementEndTime - static::$statementStartTime;
        $totalTime = round(static::$endTime - static::$startTime, 4);
        static::$totalTime += $totalTime;
        $executeTime = $totalTime - $statementTime;
        return 'Time: ' . $totalTime . '(Statemet: ' . round($statementTime, 4) . ', Execute: ' . round($executeTime, 4) . ')';
    }

    public static function add($qry, $where) {
        $data = "data(" . (is_array($where) && count($where) > 0 ? json_encode($where) : '') . ")<br />";
        static::$data[] = $qry . "<br />" . $data .
            static::buildQueryTime() . "<br />" .
            azharLoggerClass::backTrace(3);
    }

    public static function show() {
        if (static::$data !== null) {
            $i = 0;
            echo "<pre>"; //<font face=verdana size=2><strong>";
            foreach (static::$data as $value) {
                echo ++$i . ": " . $value;
                echo "<br>";
            }
            echo 'Total SQL Time: ' . static::$totalTime;
            //echo "</strong><font>";
            echo "<br>Printing Session<br>";
            print_r($_SESSION);
            echo "</pre>";
        }
    }

    public static function get() {
        return static::$data;
    }

    public static function microtime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

}
