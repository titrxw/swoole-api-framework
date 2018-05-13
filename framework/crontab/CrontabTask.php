<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-16
 * Time: 下午9:38
 */
namespace framework\crontab;

class CrontabTask
{
    protected $_taskRule;
    protected $_taskTime;

    public function __construct($taskRule)
    {
        if (empty($taskRule)) {
            throw new \Exception('task rule empty');
        }
        $this->_rule = $taskRule;
        $taskRule = explode('--', $taskRule);
        if(empty($taskRule[1])) {
            throw new \Exception('task is empty');
        }

        $this->_taskRule = $taskRule[1];
        $this->initTaskRule();
        $taskRule[0] = trim($taskRule[0], ' ');
        $this->_taskTime = new CrontabTime($taskRule[0]);
    }

    public function initTaskRule()
    {
        $this->_taskRule = str_replace('     ', ' ', $this->_taskRule);
        $this->_taskRule = str_replace('    ', ' ', $this->_taskRule);
        $this->_taskRule = str_replace('   ', ' ', $this->_taskRule);
        $this->_taskRule = str_replace('  ', ' ', $this->_taskRule);
        
        $this->_taskRule = explode(' ', $this->_taskRule);
        if (count($this->_taskRule) < 2) {
            throw new \Exception('task rule error');
        }
    }

    public function doTask($timeInfo)
    {
        if ($this->_taskTime->check($timeInfo)) {
//            添加定时任务

            return array(
                'class' => empty($this->_taskRule[0]) ? '' : $this->_taskRule[0],
                'func' => empty($this->_taskRule[1]) ? '' : $this->_taskRule[1]
            );
        }
        return null;
    }
}