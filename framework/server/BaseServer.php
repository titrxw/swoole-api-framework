<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:07
 */
namespace framework\server;
use framework\base\Base;
use framework\base\Container;

abstract class BaseServer extends Base implements ServerInterface
{
    protected $_event = null;
    protected $_server;

    protected function init()
    {
        $event = $this->getValueFromConf('event');
        $this->_server->set($this->_conf);
        $this->setEvent($event);
        $this->onConnect();
        $this->onWorkStart();
        $this->onWorkStop();
        $this->onTask();
        $this->onWorkerError();
        $this->onStart();
        $this->onShutDown();
        $this->onFinish();
    }

    public function setEvent($event)
    {
        if (empty($event))
        {
            return false;
        }

        $event = new $event();
        // TODO: Implement setEvent() method.
        if (!($event instanceof \framework\server\SwooleEvent))
        {
            unset($event);
            throw new \Error('swoole event have implement SwooleEvent', 500);
        }
        $this->_event = $event;
    }

    public function start()
    {
        // TODO: Implement start() method.
        $this->_server->start();

    }

    public function onConnect()
    {
        // TODO: Implement onConnect() method.
        $this->_server->on("Connect",function (\swoole_server $server, $client_id, $from_id)
        {
            if (empty($this->_event)) return false;
            $this->_event->onConnect($server, $client_id, $from_id);
        });
    }

    public function onStart()
    {
        // TODO: Implement onStart() method.
        $this->_server->on("start",function (\swoole_http_server $server)
        {
            if (empty($this->_event)) return false;
            $this->_event->onStart($server);
        });
    }

    public function onWorkStart()
    {
        // TODO: Implement onWorkStart() method.
        $this->_server->on("workerStart",function (\swoole_server $server, $workerId)
        {
            if (empty($this->_event)) return false;
            $this->_event->onWorkerStart($server,$workerId);
        });
    }

    public function onWorkStop()
    {
        // TODO: Implement onWorkStop() method.
        $this->_server->on("workerStop",function (\swoole_server $server, $workerId)
        {
            if (empty($this->_event)) return false;
            $this->_event->onWorkStop($server,$workerId);
        });
    }

    public function onWorkerError()
    {
        // TODO: Implement onError() method.
        $this->_server->on("workererror",function (\swoole_http_server $server,$worker_id, $worker_pid, $exit_code)
        {
            if (empty($this->_event)) return false;
            Container::getInstance()->getComponent('log')->save('workerid: ' . $worker_id . '  workerpid: ' . $worker_pid . ' code: ' . $exit_code);
            $this->_event->onWorkerError($server, $worker_id, $worker_pid, $exit_code);
        });
    }

    public function onTask()
    {
        // TODO: Implement onTask() method.
        $num = $this->getValueFromConf('task_worker_num', 0);

        if(!empty($num))
        {
            $this->_server->on("task",function (\swoole_http_server $server, $taskId, $fromId,$taskObj)
            {
                try
                {
                    if(is_string($taskObj) && class_exists($taskObj))
                    {
                        $taskObj = new $taskObj();
                    }
                    $this->_event->onTask($server, $taskId, $fromId,$taskObj);

//                    if($taskObj instanceof AbstractAsyncTask)
//                    {
//                        return $taskObj->handler($server, $taskId, $fromId);
//                    }

                    if($taskObj instanceof Closure)
                    {
                        return $taskObj($server, $taskId);
                    }
                    return null;
                }
                catch (\Exception $exception){
                    throw $exception;
                }
            });
        }
    }

    public function onShutdown()
    {
        // TODO: Implement onShutDown() method.
        $this->_server->on("shutdown",function (\swoole_http_server $server){
            if (empty($this->_event)) return false;
            $this->_event->onShutdown($server);
        });
    }

    public function onFinish()
    {
        // TODO: Implement onFinish() method.
        $num = $this->getValueFromConf('task_worker_num', 0);
        if(!empty($num))
        {
            $this->_server->on("finish", function (\swoole_http_server $server, $taskId, $taskObj)
            {
                try
                {
                    $this->_event->onFinish($server, $taskId, $taskId,$taskObj);
                    $this->_server->finish();
                }
                catch (\Exception $exception)
                {
                    throw $exception;
                }
            });
        }
    }
}