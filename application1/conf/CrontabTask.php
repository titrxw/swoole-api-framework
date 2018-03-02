<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-18
 * Time: 下午12:18
 */
namespace application1\conf;

use framework\task\BaseTask;

class CrontabTask extends BaseTask
{
    public function test($params = array(), $server, $taskId, $fromId)
    {
        var_dump('test do');
    }

//    该方法是sendMsg的结束方法
    public function testFinish($result = array(), $server, $taskId, $fromId)
    {
        $this->getComponent('log')->save('test finish');
    }
}