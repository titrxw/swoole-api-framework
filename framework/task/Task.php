<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午9:52
 */
namespace framework\task;

use framework\base\Component;
//use function Sodium\crypto_aead_aes256gcm_encrypt;

class Task extends Component
{
    public function addTask($taskClass, $taskName, $params = [], $taskId = -1)
    {
        if (!$taskClass || !$taskName || !is_string($taskClass) || !is_string($taskName))
        {
            return false;
        }
        $this->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addTask(array(
            'system' => \getModule(),
            'class' => $taskClass,
            'func' => $taskName,
            'params' => $params
        ), $taskId);
    }

    public function addAsyncTask($taskClass, $taskName, $params = [], $taskId = -1)
    {
        if (!$taskClass || !$taskName)
        {
            return false;
        }
        $this->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addAsyncTask(array(
            'system' => \getModule(),
            'class' => $taskClass,
            'func' => $taskName,
            'params' => $params
        ), $taskId);
    }
}