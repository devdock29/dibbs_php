<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funsocio.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2023 FUNSOCIO All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\helpers;

class MultiProcess {
    
    public $directory = '/tmp/';
    private $name = null;
    private $count = null;
    private $path = null;
    private $oldProcessMinutes = 15;
    private static $sleep = 0.5;
    private static $maxAllowed = 10;
    
    public function __construct ($options = []) {
        $this->validateOptions($options);
        $this->name = $options['name'];
        $this->count = (int) $options['count'];
        $this->path = $this->directory . $this->name;
    }
    
    private function validateOptions($options) {
        $requiredOptions = ['name', 'count'];
        foreach ($requiredOptions as $requiredOption) {
            if (empty($options[$requiredOption])) {
                throw new \Exception('Process ' . $requiredOption . ' cannot be empty');
            }
        }
        if ((int) $options['count'] > self::$maxAllowed || (int) $options['count'] < 1) {
            throw new \Exception('Process count cannot be greater than ' . self::$maxAllowed . ' or less than 1');
        }
        return true;
    }
    
    private function processCount() {
        if (!file_exists($this->path)) {
            return 0;
        }
        return count(scandir($this->path)) - 2;
    }
    
    public function startChild($cmd = '') {
        while($this->processCount() >= $this->count) {
            sleep(self::$sleep);
        }
        $cmd .= ' > /dev/null 2>/dev/null &';
        exec($cmd);
    }
    
    private function createChildProcess() {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0777);
        }
        $pid = getmypid();
        $lockFile = "{$this->path}/$pid.lock";
        system("echo 'locked' > $lockFile");
        return $lockFile;
    }
    
    public function removeAllProcesses() {
        @system('rm -rf ' . $this->path);
    }
    
    public function removeOldProcesses() {
        if (!file_exists($this->path)) {
            return;
        }
        $oldTime = strtotime('-' . $this->oldProcessMinutes . ' minutes');
        $processes = scandir($this->path);
        foreach ($processes as $process) {
            if ($process == '.' or $process == '..') {
                continue;
            }
            $processTime = filemtime($this->path . '/' . $process);
            if ($processTime < $oldTime) {
                unlink($this->path . '/' . $process);
            }
        }
    }
    
    private function removeChildProcess($childProcess) {
        unlink($childProcess);
    }
    
    public function executeChild($child = []) {
        $childProcess = $this->createChildProcess();
        $child['object']->$child['method']($child['args']);
        $this->removeChildProcess($childProcess);
    }
}
