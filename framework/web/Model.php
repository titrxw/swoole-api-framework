<?php
namespace framework\web;


class Model extends \framework\base\Model
{
    protected function addTask($className, $funcName, $params,$taskId = -1, $isAsync = false)
    {
        if (!ISSWOOLE) {
            $this->triggerThrowable(new \Error('addTask not support ', 500));
        }
        if (!$isAsync)
        {
            $this->taskManager->addTask($className, $funcName, $params, $taskId);
        }
        else
        {
            $this->taskManager->addAsyncTask($className, $funcName, $params, $taskId);
        }
    }
}