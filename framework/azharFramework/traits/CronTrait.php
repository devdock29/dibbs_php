<?php

// +------------------------------------------------------------------------+
// | @author Azhar Waris (AzharJutt)
// | @author_url: http://www.funbook-pk.com/azhar
// | @author_email: azharwaris@gmail.com
// +------------------------------------------------------------------------+
// | Copyright (c) 2017 FUNBOOK. All rights reserved.
// +------------------------------------------------------------------------+

namespace azharFramework\traits;

trait CronTrait {

    protected $iterationSize = 1000;
    protected $start = 0;
    protected $iterations;
    private $iterationNumber;
    protected $totalRecords;
    protected $whereClause;
    protected $whereParams;
    protected $debug = false;

    public function setIterationSize($iterationSize) {
        $this->iterationSize = $iterationSize;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function init() {
        $this->totalRecords = $this->calculateCount();
        $this->iterations = $this->calculateIterations();
    }

    public function calculateIterations() {
        return ceil($this->totalRecords / $this->iterationSize);
    }

    public function printMainStats() {
        echo "Total Records:" . $this->totalRecords . "\n";
        echo "Total Iterations:" . $this->iterations . "\n";
        echo "Iteration Size:" . $this->iterationSize . "\n";
    }

    public function printIteration() {
        echo "\nIteration Numeber:" . $this->iterationNumber . "\n";
    }

    public function calculateCount() {
        return $this->count(array('whereClause' => $this->whereClause, 'whereParams' => $this->whereParams));
    }

    public function getIterations() {
        return $this->iterations;
    }

    public function getIterationNumber() {
        return $this->iterationNumber;
    }

    public function getTotalRecords() {
        return $this->totalRecords;
    }

    public function beforeRun() {

    }

    public function limit() {
        $query = explode("LIMIT", $this->whereClause);
        $this->whereClause = trim($query[0]) . ' LIMIT ' . $this->start . ',' . $this->iterationSize;
    }

    public function run($totalIterations = 0) {
        $this->beforeRun();
        $this->init();
        $this->printMainStats();
        $iterations2run = $this->iterations;
        if ($totalIterations > 0) {
            $iterations2run = $totalIterations;
        }
        for ($this->iterationNumber = 0; $this->iterationNumber < $iterations2run; $this->iterationNumber++) {
            $this->build();
            $this->start += $this->iterationSize;
        }
        $this->afterRun();
    }

    public function afterRun() {

    }

    abstract public function build();

    abstract public function manipulateRow($row);
}
