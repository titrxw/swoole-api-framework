<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午7:57
 */
namespace framework\server;

interface ServerInterface
{
    public function start();
    public function onConnect();
    public function onStart();
    public function onShutdown();
    public function onWorkStart();
    public function onWorkStop();
    public function onTask();
    public function onPipMessage();
    public function onWorkerError();
    public function onFinish();
    public function setEvent($event);
}