<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午9:41
 */
namespace  framework\task;
use framework\base\Component;

abstract class BaseTask extends Component
{
    protected function init()
    {
        // 执行完成后释放
        $this->unInstall();
    }

    public function run($funcName, $params = [], $server, $taskId, $fromId)
    {
        if (!$funcName)
        {
            $this->triggerThrowable(new \Error('function not be empty', 500));
        }
        if (!method_exists($this, $funcName))
        {
            $this->triggerThrowable(new \Error('function ' . $funcName . ' not exists', 500));
        }
        return $this->$funcName($params, $server, $taskId, $fromId);
    }
}