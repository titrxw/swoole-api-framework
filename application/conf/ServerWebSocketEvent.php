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

class ServerWebSocketEvent implements SwooleEvent
{
    public $_connections = array();

    public function onHandShake(\swoole_http_request $request, \swoole_http_response $response)
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
        // TODO: Implement onWorkerStart() method.
//        var_dump($workerId);
        if ($workerId < Container::getInstance()->getComponent('server')->getServer()->getWorkerNum()) {
            Container::getInstance()->getComponent('server')->getServer()->addTimer(6000, function ($timer_id, $params) {
                var_dump($timer_id);
                Container::getInstance()->getComponent('crontab')->run();
            });
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

    public function onClose(\swoole_websocket_server $server, $frame)
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