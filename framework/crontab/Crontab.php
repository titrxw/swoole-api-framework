<?php
namespace framework\crontab;

use framework\base\Component;

class Crontab extends Component
{
    protected $_tasks = [];

    protected function init()
    {
        if (empty($this->_conf['tasks'])) {
            return false;
        }
        if (!is_array($this->_conf['tasks']))
        {
            throw new \Exception('crontab task error');
        }
        foreach ($this->_conf['tasks'] as $item)
        {
            $this->_tasks[] = new CrontabTask($item);
        }
    }

    protected function clear()
    {
        unset($this->_tasks);
        $this->_tasks = [];
        $this->_conf['tasks'] = [];
    }

    protected function getTime()
    {
        $time = date('Y-m-d H:i:s');
        $time = explode(' ', $time);
        $ymd = $time[0];
        $hm = $time[1];
        $ymd = explode('-', $ymd);
        $hm = explode(':', $hm);
 
        $timeInfo['sec'] = ltrim($hm[2], '0');
        $timeInfo['month'] = ltrim($ymd[1], '0');
        $timeInfo['day'] = ltrim($ymd[2], '0');
        $timeInfo['hour'] = ltrim($hm[0], '0');
        $timeInfo['min'] = ltrim($hm[1], '0');
        $timeInfo['week'] = date('w');
        $timeInfo['week'] =  $timeInfo['week'] == 0 ? 7 :  $timeInfo['week'];
        return $timeInfo;
    }

    public function updateLatestTask($tasks)
    {
        $tmpTasks = [];
        try{
            foreach ($tasks as $value) {
                # code...
                $tmpTasks[] = new CrontabTask($value);
            }
        } catch (\Throwable $e) {
            unset($tmpTasks);
            $this->handleThrowable($e);
            return false;
        }
        
        $this->clear();
        $this->_tasks = $tmpTasks;
        $this->_conf['tasks'] = $tasks;
        return true;
    }

    public function addTask($rule)
    {
        try{
            $this->_tasks[] = new CrontabTask($rule);
            $this->_conf['tasks'][] = $rule;
        } catch (\Throwable $e) {
            $this->handleThrowable($e);
            return false;
        }

        return true;
    }

    public function run()
    {
        $timeInfo = $this->getTime();
        foreach ($this->_tasks as $item)
        {
            yield $item->doTask($timeInfo);
        }
    }
}