<?php

namespace framework\crontab;

class CrontabTime
{
    protected $_rule;
    protected $_minTime;
    protected $_hourTime;
    protected $_dayTime;
    protected $_monthTime;
    protected $_weekTime;

    public function __construct($rule)
    {
        if (empty($rule)) {
            throw new \Exception('rule empty ');
        }
        $rule = str_replace('     ', ' ', $rule);
        $rule = str_replace('    ', ' ', $rule);
        $rule = str_replace('   ', ' ', $rule);
        $rule = str_replace('  ', ' ', $rule);
        
        $_rule = explode(' ', $rule);
        if (count($_rule) != 5) {
            throw new \Exception('rule error ' . $rule);
        }

        $this->_minTime = new TaskTime($_rule[0]);
        $this->_hourTime = new TaskTime($_rule[1]);
        $this->_dayTime = new TaskTime($_rule[2]);
        $this->_monthTime = new TaskTime($_rule[3]);
        $this->_weekTime = new TaskTime($_rule[4]);
    }

    public function check($obj)
    {
        if ($this->_minTime->check($obj['min']) &&
            $this->_hourTime->check($obj['hour']) &&
            $this->_dayTime->check(($obj['day'])) &&
            $this->_monthTime->check($obj['month']) &&
            $this->_weekTime->check($obj['week'])) {
            return true;
        }
    }
}