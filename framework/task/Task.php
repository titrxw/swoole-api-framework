<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午9:52
 */
namespace framework\task;

use framework\base\Component;

class Task extends Component
{
    public function addTask($taskClass, $taskName, $params, $taskId = -1)
    {
        if (empty($taskClass) || empty($taskName) || !is_string($taskClass) || !is_string($taskName))
        {
            return false;
        }
        $this->getComponent('server')->getServer()->addTask(array(
            'class' => $taskClass,
            'func' => $taskName,
            'params' => $params
        ), $taskId);
    }

    public function addAsyncTask($taskClass, $taskName, $params, $taskId = -1)
    {
        if (empty($taskClass) || empty($taskName))
        {
            return false;
        }
        $this->getComponent('server')->getServer()->addAsyncTask(array(
            'class' => $taskClass,
            'func' => $taskName,
            'params' => $params
        ), $taskId);
    }
}