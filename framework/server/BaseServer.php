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
use framework\process\Manager;
use framework\process\AutoReloadProcess;
use framework\task\BaseTask;

abstract class BaseServer extends Base implements ServerInterface
{
    protected $_pManager;
    protected $_event = null;
    protected $_server;
    protected $_maxTickStep = 86400000;
    protected $_isStart = false;
    protected $_taskWorkerNum = -1; 
    protected $_workNum = 0;

    protected function init()
    {
//        防止重新启动
        if ($this->_isStart) return false;
        if (!$this->_server) {
            $this->_server = new \swoole_server($this->_conf['ip'], $this->_conf['port'],$this->getValueFromConf('mode', SWOOLE_PROCESS), $this->getValueFromConf('_type', SWOOLE_SOCK_TCP));
            $this->onReceive();
          }
        $this->_server->set($this->_conf);
        if (empty($this->_conf['worker_num']) || $this->_conf['worker_num'] <= 0) {
            $this->_workNum = \swoole_cpu_num();
        } else {
            $this->_workNum = $this->_conf['worker_num'];
        }
                
        $this->setEvent($this->getValueFromConf('event'));
        $this->onConnect();
        $this->onClose();
        $this->onWorkStart();
        $this->onWorkStop();
        $this->onTask();
        $this->onWorkerError();
        $this->onStart();
        $this->onReceive();
        $this->onShutDown();
        $this->onFinish();
        $this->onPipMessage();
        $this->onManagerStart();
        $this->onManagerStop();
    }

    protected function getProcessManager()
    {
        if (!$this->_pManager) {
            $this->_pManager = new Manager();
        }
        return $this->_pManager;
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

    protected function afterStart(\swoole_server $server)
    {
        return true;
    }

    protected function addProcess($server)
    {
        $this->_pManager = new Manager();
        if (!empty($this->_conf['zookeeper'])) {
            $this->_pManager->addProcess(new ZookeeperProcess());
        }
        if (DEBUG) {
            $this->getProcessManager();
            $auto =new AutoReloadProcess();
            $auto->setServerPid($server->master_pid);
            $this->_pManager->addProcess($auto);
        }
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
                if ($this->getValueFromConf('mode') == SWOOLE_BASE) {
                    $this->addProcess($server);
                }

                $this->afterStart($server);

                if ($this->getValueFromConf('mode') == SWOOLE_BASE) {
                    if ($this->_pManager) {
                        $this->_pManager->start();
                    }
                }
                swoole_set_process_name('master');
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }
        });
    }

    protected function afterManagerStart(\swoole_server $server)
    {
        return true;
    }

    protected function onManagerStart()
    {
        $this->_server->on("managerStart",function (\swoole_server $server)
        {
            try
            {
                $this->addProcess($server);
                $this->afterManagerStart($server);
                
                if ($this->_pManager) {
                    $this->_pManager->start();
                }
                swoole_set_process_name('manager');
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
            }
        });
    }

    protected function afterManagerStop(\swoole_server $server)
    {
        
        return true;
    }

    protected function onManagerStop()
    {
        $this->_server->on("managerStop",function (\swoole_server $server)
        {
            try
            {
                $this->afterManagerStop($server);
                if ($this->_pManager) {
                    $this->_pManager->kill();
                }
                Container::getInstance()->end();
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
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
            if ($this->isWork($workerId)) {
                swoole_set_process_name('worker');
            } else {
                swoole_set_process_name('task-worker');
            }
            
            //\opcache_reset();
            \define('SYSTEM_WORK_ID', \getmypid());
            try
            {
                if ($this->_event) {
                    $this->_event->onWorkerStart($server,$workerId);
                }

                $this->afterWorkStart($server, $workerId);
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
            }
        });
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
                $this->handleThrowable($e);
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
                $this->handleThrowable($e);
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
                $this->handleThrowable($e);
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
                Container::getInstance()->end();
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
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
            try
            {
                Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'log')->save('workerid: ' . $worker_id . '  workerpid: ' . $worker_pid . ' code: ' . $exit_code);
                if ($this->_event) {
                    $this->_event->onWorkerError($server, $worker_id, $worker_pid, $exit_code);
                }

                $this->afterWorkerError($server, $worker_id, $worker_pid, $exit_code);
                Container::getInstance()->end();
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
            }
        });
    }

    protected function afterPipMessage(\swoole_server $serv, $src_worker_id, $data)
    {
        return true;
    }

    public function onPipMessage()
    { 
        // TODO: Implement onShutDown() method.
        $this->_server->on("pipeMessage",function (\swoole_server $serv, $src_worker_id, $data){
            try
            {
                if ($this->_event) {
                    $this->_event->onPipMessage($serv, $src_worker_id, $data);
                }

                $this->afterPipMessage( $serv, $src_worker_id, $data);
            }
            catch (\Throwable $e)
            {
                $this->handleThrowable($e);
            }
        });
    }

    protected function doTask($taskObj) 
    {
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
                    $this->triggerThrowable(new \Error('task at do: id: ' . $taskId . ' class: ' . $taskObj['class'] . 'not found or not instance BaseTask'.
                        ' or action: ' .$taskObj['func'] . ' not found', 500));
                }
            }
        } else if($taskObj instanceof \Closure) {
            return $taskObj($server, $taskId, $fromId);
        } else {
            $this->triggerThrowable(new \Error('task at do: id: arg error: ' . \json_encode($taskObj), 500));
        }

        return $taskObj;
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
                    $this->doTask($taskObj);
                    
                }
                catch (\Throwable $e)
                {
                    $this->handleThrowable($e);
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
                $this->handleThrowable($e);
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
                    
                    $this->handleThrowable($e);
                    return false;
                }
                finally 
                {
                    Container::getInstance()->finish(SYSTEM_APP_NAME);
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
        return $this->_workNum;
    }

    protected function isWork($pid)
    {
        if ($pid < $this->_workNum) {
            return true;
        }
        return false;
    }
    

    protected function isTask($pid)
    {
        if ($pid >= $this->_workNum) {
            return true;
        }
        return false;
    }
}
