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
