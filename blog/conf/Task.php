<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午10:25
 */
namespace blog\conf;

use framework\task\BaseTask;

class Task extends BaseTask
{
    public function sendMsg($params = array(), $server, $taskId, $fromId)
    {
        for ($i=0;$i<10;$i++)
        {
            $this->getComponent(SYSTEM_APP_NAME, 'log')->save(serialize($params));
        }
    }

//    该方法是sendMsg的结束方法
    public function sendMsgFinish($result = array(), $server, $taskId, $fromId)
    {
        $this->getComponent(SYSTEM_APP_NAME,'log')->save('finish');
    }
}