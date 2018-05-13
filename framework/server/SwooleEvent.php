<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:13
 */
namespace framework\server;

interface SwooleEvent
{
    public function onConnect(\swoole_server $server, $client_id, $from_id);
    public function onStart(\swoole_server $server);
    public function onShutdown(\swoole_server $server);
    public function onWorkerStart(\swoole_server $server, $workerId);
    public function onWorkerStop(\swoole_server $server, $workerId);
    public function onTask(\swoole_server $server, $taskId, $fromId,$taskObj);
    public function onWorkerError(\swoole_server $server,$worker_id, $worker_pid, $exit_code);
    public function onFinish(\swoole_server $server, $taskId, $taskObj);
}