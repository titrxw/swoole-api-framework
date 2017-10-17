<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午10:25
 */
namespace application\conf;

use framework\task\BaseTask;

class Task extends BaseTask
{
    public function sendMsg($params = array(), $server, $taskId, $fromId)
    {
        for ($i=0;$i<10;$i++)
        {
            $this->getComponent('log')->save(serialize($params));
        }
    }

//    该方法是sendMsg的结束方法
    public function sendMsgFinish($result = array(), $server, $taskId, $fromId)
    {
        $this->getComponent('log')->save('finish');
    }
}