<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-18
 * Time: 下午12:18
 */
namespace blog\conf;

use framework\task\BaseTask;

class CrontabTask extends BaseTask
{
    public function test($params = array(), $server, $taskId, $fromId)
    {
        sleep(5);
        echo 'task';
//        $lock = $this->getComponent('redis');
//
//        $id = $lock->lock('testlock',2,0);
//        if ($id !== false) {
//            var_dump($fromId.' got lock');
////            sleep(2);
//           $lock->unLock('testlock', $id);
//
//        } else {
//            var_dump($fromId.' have not got lock');
//        }
    }

//    该方法是sendMsg的结束方法
    public function testFinish($result = array(), $server, $taskId, $fromId)
    {
        $this->getComponent('log')->save('test finish');
    }
}