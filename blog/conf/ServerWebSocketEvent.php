<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-17
 * Time: 下午2:09
 */

namespace blog\conf;

use framework\base\Container;
use framework\server\SwooleEvent;
use framework\task\BaseTask;

class ServerWebSocketEvent implements SwooleEvent
{
    public $_connections = array();

    public function onHandShake(\swoole_server $request, \swoole_http_response $response)
    {

    }

    public function onConnect(\swoole_server $server, $client_id, $from_id)
    {
        // TODO: Implement onConnect() method.
    }

    public function onOpen(\swoole_websocket_server $server, $frame)
    {

    }

    public function onWorkerStart(\swoole_server $server, $workerId)
    {
//            开启数据库将断开的检测   8小时检测
        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addTimer(28800000, function ($timer_id, $params) {
            Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'meedo')->pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
        });
        if ($workerId==0) {

            $task_process = new \swoole_process(function(\swoole_process $worker){
                swoole_set_process_name('php-task-crontab');
                while(true)
                {
                    try{
                        $taskObj = $worker->pop();
                        $taskObj = json_decode($taskObj, true);
                        if (is_array($taskObj))
                        {
                            if (!empty($taskObj['class']) && !empty($taskObj['func']))
                            {
                                $obj = Container::getInstance()->getComponent($taskObj['class']);

                                if ($obj && $obj instanceof BaseTask)
                                {
                                    $obj->run($taskObj['func'], array(), $worker, 0, 0);
                                    unset($obj);
                                }
                                else
                                {
                                    throw new \Exception('task at do:  class: ' . $taskObj['class'] . 'not found or not instance BaseTask'.
                                        ' or action: ' .$taskObj['func'] . ' not found', 500);
                                }
                            }
                        }
                    } catch(\Exception $e) {
                        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'exception')->handleException($e);
                    }
                }
            }, false, false);

            $crontab_process = new \swoole_process(function(\swoole_process $worker) use ($task_process){
                swoole_set_process_name('php-crontab');
                while(true)
                {
                    foreach(Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'crontab')->run() as $task_item) {
                        if (!empty($task_item)) {
                            $task_process->push(json_encode($task_item));
                        }
                    }
                    // 每秒执行一次
                    sleep(1);
                }
            }, false, false);
            
//             $task_process->useQueue();
//             $pid=$task_process->start();
//             // 因为crontab process use 了 task process 所以要在task process 启动后才可以启动crontab process  要不然会导致push的时候无法push
//             $pid=$crontab_process->start();
//             \swoole_process::signal(SIGCHLD, function($sig) {
//                 //必须为false，非阻塞模式
//                 while($ret =  \swoole_process::wait(false)) {
//                     echo "PID={$ret['pid']}\n";
//                 }
//             });
        }
    }

    public function onWorkStop(\swoole_server $server, $workerId)
    {

    }

    public function onMessage(\swoole_websocket_server $server, &$frame)
    {
//        $frame->data = array(
//            'controller' => 'index',
//            'action' => 'test'
//        );
    }

    public function onClose(\swoole_server $server, $fd, $reactorId)
    {
//        unset($this->_connections[$frame->fd]);
    }


    public function onRequest(\swoole_http_request $request,\swoole_http_response $response)
    {

    }

    public function onResponse(\swoole_http_request $request,\swoole_http_response $response)
    {

    }

    public function onWorkerStop(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }

    public function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onTask(\swoole_server $server, $taskId, $fromId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    public function onStart(\swoole_server $server)
    {
        // TODO: Implement onStart() method.
    }

    public function onFinish(\swoole_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    public function onShutdown(\swoole_server $server)
    {
        // TODO: Implement onShutdown() method.
    }

    public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {

    }

    public function onPipMessage(\swoole_server $serv, $src_worker_id, $data)
    {

    }
}