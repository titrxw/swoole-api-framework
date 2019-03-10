<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-17
 * Time: 下午2:09
 */

namespace framework\web;

use framework\base\Container;
use framework\server\SwooleEvent;
use framework\process\ZookeeperProcess;

class ServerEvent implements SwooleEvent
{
    public function onConnect(\swoole_server $server, $client_id, $from_id)
    {
        // TODO: Implement onConnect() method.
    }

    public function onManagerStart(\swoole_server $server)
    {
        $server = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'server');
        $pManager = $server->getProcessManager();
        $pManager->addProcess(new ZookeeperProcess());
    }

    public function onManagerStop(\swoole_server $server)
    {

    }

    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addTimer(28800000, function ($timer_id, $params) {
            try{
                global $ALL_MODULES;
                foreach($ALL_MODULES as $key => $item) {
                    Container::getInstance()->getComponent($key, 'meedo')->pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
                }
            } catch (\Throwable $e) {
                $this->handleThrowable($e);
            }
        });

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
        $server = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'server');
        if ($server->getMode() == SWOOLE_BASE) {
            $pManager = $server->getProcessManager();
            $pManager->addProcess(new ZookeeperProcess());
        }
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