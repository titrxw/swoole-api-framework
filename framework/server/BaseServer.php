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
use framework\task\BaseTask;

abstract class BaseServer extends Base implements ServerInterface
{
    /**
     * @var null
     * 使用trait 添加triggerException 方法
     */

    protected $_event = null;
    protected $_server;
    protected $_maxTickStep = 86400000;
    protected $_isStart = false;
    protected $_taskWorkerNum = -1; 

    protected function init()
    {
//        防止重新启动
        if ($this->_isStart) return false;
        $this->_server->set($this->_conf);
        $this->setEvent($this->getValueFromConf('event'));
        $this->onConnect();
        $this->onClose();
        $this->onReceive();
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
        if (!$event)
        {
            return false;
        }

        $event = new $event();
        // TODO: Implement setEvent() method.
        if (!($event instanceof \framework\server\SwooleEvent))
        {
            unset($event);
            $this->triggerThrowable(new \Error('swoole event have implement SwooleEvent', 500));
        }
        $this->_event = $event;
    }

    public function start()
    {
        // TODO: Implement start() method.

//        防止重新启动
        if ($this->_isStart) return false;
        $this->_isStart = true;
        $this->_server->start();
    }

    protected function afterConnect(\swoole_server $server, $client_id, $from_id)
    {
        return true;
    }

    public function onConnect()
    {
        // TODO: Implement onConnect() method.
        $this->_server->on("Connect",function (\swoole_server $server, $client_id, $from_id)
        {
            try
            {
                if ($this->_event) {
                    $this->_event->onConnect($server, $client_id, $from_id);
                }

                $this->afterConnect($server, $client_id, $from_id);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterClose(\swoole_server $server, int $fd, int $reactorId)
    {
        return false;
    }

    public function onClose()
    {
        // TODO: Implement onConnect() method.
        $this->_server->on("close",function (\swoole_server $server, int $fd, int $reactorId)
        {
            try
            {
                if ($this->_event) {
                    $this->_event->onClose($server, $fd, $reactorId);
                }

                $this->afterClose($server, $fd, $reactorId);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        return false;
    }

    public function onReceive ()
    {
        $this->_server->on('receive', function (\swoole_server $serv, $fd, $from_id, $data) {
            try
            {
                if ($this->_event) {
                    $this->_event->onReceive($serv, $fd, $from_id, $data);
                }
                $this->afterReceive($serv, $fd, $from_id, $data);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterStart(\swoole_server $serv)
    {
        return true;
    }

    public function onStart()
    {
        // TODO: Implement onStart() method.
        $this->_server->on("start",function (\swoole_server $server)
        {
            try
            {
                if ($this->_event) {
                    $this->_event->onStart($server);
                }

                $this->afterStart($server);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterWorkStart(\swoole_server $serv, $workerId)
    {
        return true;
    }

    public function onWorkStart()
    {
        // TODO: Implement onWorkStart() method.
        $this->_server->on("workerStart",function (\swoole_server $server, $workerId)
        {
            //\opcache_reset();
            define('SYSTEM_WORK_ID', $workerId);
            try
            {
                if ($this->_event) {
                    $this->_event->onWorkerStart($server,$workerId);
                }

                $this->afterWorkStart($server, $workerId);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterWorkStop(\swoole_server $serv, $workerId)
    {
        return true;
    }

    public function onWorkStop()
    {
        // TODO: Implement onWorkStop() method.
        $this->_server->on("workerStop",function (\swoole_server $server, $workerId)
        {
            try
            {
                if ($this->_event) {
                    $this->_event->onWorkStop($server,$workerId);
                }

                $this->afterWorkStop($server, $workerId);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
    {
        return true;
    }

    public function onWorkerError()
    {
        // TODO: Implement onError() method.
        $this->_server->on("workererror",function (\swoole_server $server,$worker_id, $worker_pid, $exit_code)
        {
            Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'log')->save('workerid: ' . $worker_id . '  workerpid: ' . $worker_pid . ' code: ' . $exit_code);
            if ($this->_event) {
                $this->_event->onWorkerError($server, $worker_id, $worker_pid, $exit_code);
            }

            $this->afterWorkerError($server, $worker_id, $worker_pid, $exit_code);
        });
    }

    public function onTask()
    {
        // TODO: Implement onTask() method.
        $num = $this->getTaskWorkerNum();
        if($num)
        {
            $this->_server->on("task",function (\swoole_server $server, $taskId, $fromId,$taskObj)
            {
                try
                {
                    if ($this->_event) {
                        $this->_event->onTask($server, $taskId, $fromId, $taskObj);
                    }
                    if (is_array($taskObj))
                    {
                        if (!empty($taskObj['class']) && !empty($taskObj['func']))
                        {
                            $obj = Container::getInstance()->getComponent(SYSTEM_APP_NAME, $taskObj['class']);

                            if ($obj && $obj instanceof BaseTask)
                            {
                                $obj->run($taskObj['func'], $taskObj['params'] ?? [], $server, $taskId, $fromId);
                                unset($obj);
                            }
                            else
                            {
                                $this->triggerThrowable(new \Exception('task at do: id: ' . $taskId . ' class: ' . $taskObj['class'] . 'not found or not instance BaseTask'.
                                    ' or action: ' .$taskObj['func'] . ' not found', 500));
                            }
                        }
                    }

                    if($taskObj instanceof \Closure)
                    {
                        return $taskObj($server, $taskId, $fromId);
                    }

                    return $taskObj;
                }
                catch (\Throwable $e)
                {
                    $this->triggerThrowable($e);
                    return false;
                }
            });
        }
    }

    protected function afterShutdown(\swoole_server $server)
    {
        return true;
    }

    public function onShutdown()
    {
        // TODO: Implement onShutDown() method.
        $this->_server->on("shutdown",function (\swoole_server $server){
            try
            {
                if ($this->_event) {
                    $this->_event->onShutdown($server);
                }

                $this->afterShutdown($server);
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    public function onFinish()
    {
        // TODO: Implement onFinish() method.
        $num = $this->getTaskWorkerNum();
        if($num)
        {
            $this->_server->on("finish", function (\swoole_server $server, $taskId, $taskObj)
            {
                try
                {
                    if ($this->_event) {
                        $this->_event->onFinish($server, $taskId, $taskId,$taskObj);
                    }
                    if (is_array($taskObj))
                    {
                        if (!empty($taskObj['class']) && !empty($taskObj['func']))
                        {
                            $obj = Container::getInstance()->getComponent(SYSTEM_APP_NAME, $taskObj['class']);

                            if ($obj && $obj instanceof BaseTask)
                            {
                                $obj->run($taskObj['func'].'Finish', $taskObj['params'] ?? [],  $server, $taskId, -1);
                                unset($obj);
                                Container::getInstance()->destroyComponentsInstance(SYSTEM_APP_NAME, $taskObj['class']);
                            }
                            else
                            {
                                $this->triggerThrowable(new \Exception('task at finish: id: ' . $taskId . ' class: ' . $taskObj['class'] . 'not found or not instance BaseTask'.
                                    ' or action: ' .$taskObj['func'] . ' not found', 500));
                            }
                        }
                    }

                    return false;
                }
                catch (\Throwable $e)
                {
                    $this->triggerThrowable($e);
                    return false;
                }
            });
        }
    }

    public function addTask($data, $taskId)
    {
        $num = $this->getTaskWorkerNum();
        if ($num <= 0)
        {
            return false;
        }
        return $this->_server->task($data, $taskId);
    }

    public function addAsyncTask($data, $taskId)
    {
        $num = $this->getTaskWorkerNum();
        if ($num <= 0)
        {
            return false;
        }
        return $this->_server->taskwait($data, $taskId);
    }

    public function addTimer($timeStep, callable $callable, $params= [])
    {
        if (!is_integer($timeStep)) return false;
        if ($timeStep === 0) return false;
        if ($timeStep > $this->_maxTickStep) return false;
        return swoole_timer_tick($timeStep, $callable, $params);
    }

    public function addTimerAfter($timeStep, callable $callable, $params= [])
    {
        if (!is_integer($timeStep)) return false;
        if ($timeStep === 0) return false;
        if ($timeStep > $this->_maxTickStep) return false;
        return swoole_timer_after($timeStep, $callable, $params);
    }

    public function getTaskWorkerNum()
    {
        if ($this->_taskWorkerNum < 0) {
            $this->_taskWorkerNum = $this->getValueFromConf('task_worker_num', 0);
        }
        return $this->_taskWorkerNum;
    }

    public function getWorkerNum()
    {
        return $this->getValueFromConf('worker_num', 0);
    }

    protected function isWork($pid)
    {
        if ($pid < $this->getValueFromConf('worker_num', 1)) {
            return true;
        }
        return false;
    }
    

    protected function isTask($pid)
    {
        if ($pid >= $this->getValueFromConf('worker_num', 1)) {
            return true;
        }
        return false;
    }
}