<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-17
 * Time: 下午2:09
 */

namespace application\conf;

use framework\base\Container;
use framework\server\SwooleEvent;

class ServerEvent implements SwooleEvent
{
    public function onConnect(\swoole_server $server, $client_id, $from_id)
    {
        // TODO: Implement onConnect() method.
    }

    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
        // if ($workerId < Container::getInstance()->getComponent('server')->getServer()->getValueFromConf('worker_num', 0)) {
        //     Container::getInstance()->getComponent('server')->getServer()->addTimer(6000, function ($timer_id, $params) {
        //         var_dump($timer_id);
        //         Container::getInstance()->getComponent('crontab')->run();
        //     });
        // }

        if ($workerId==0) {
            $process = new \swoole_process(function(\swoole_process $worker){
                swoole_set_process_name('php-crontab');
                while(true)
                {
                    Container::getInstance()->getComponent('crontab')->run();
                    sleep(1);
                }
                
            }, false, false);
            $pid=$process->start();
            $ret = \swoole_process::wait();
            if ($ret) {
                echo 'crontab exit';
            }
        }
    }

    public function onWorkStop(\swoole_server $server, $workerId)
    {

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

    public function onWorkerError(\swoole_http_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onTask(\swoole_http_server $server, $taskId, $fromId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    public function onStart(\swoole_http_server $server)
    {
        // TODO: Implement onStart() method.
    }

    public function onFinish(\swoole_http_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    public function onShutdown(\swoole_http_server $server)
    {
        // TODO: Implement onShutdown() method.
    }
}