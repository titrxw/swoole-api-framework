<?php
namespace framework\crontab;

use framework\base\Component;

class Crontab extends Component
{
    protected $_tasks = [];

    protected function init()
    {
        if (empty($this->_appConf['tasks'])) {
            return false;
        }
        if (!is_array($this->_appConf['tasks']))
        {
            throw new \Exception('crontab task error');
        }
        foreach ($this->_appConf['tasks'] as $item)
        {
            $this->_tasks[] = new CrontabTask($item);
        }
    }

    public function addTask($rule)
    {
        $this->_tasks[] = new CrontabTask($rule);
    }

    public function run()
    {
        $timeInfo = $this->getTime();

        foreach ($this->_tasks as $item)
        {
            yield $item->doTask($timeInfo);
        }
    }

    protected function getTime()
    {
        $time = date('Y-m-d H:i:s');
        $time = explode(' ', $time);
        $ymd = $time[0];
        $hm = $time[1];
        $ymd = explode('-', $ymd);
        $hm = explode(':', $hm);

        $timeInfo['month'] = ltrim($ymd[1], '0');
        $timeInfo['day'] = ltrim($ymd[2], '0');
        $timeInfo['hour'] = ltrim($hm[0], '0');
        $timeInfo['min'] = ltrim($hm[1], '0');
        $timeInfo['week'] = date('w');
        return $timeInfo;
    }
}