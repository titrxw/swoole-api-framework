<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;
use framework\base\Container;

class CrontabServer extends BaseServer
{
    protected function init()
    {
        $this->_server = new \swoole_server($this->_conf['ip'], $this->_conf['port']);
        parent::init(); // TODO: Change the autogenerated stub
        $this->onRecive();
    }

    public function onWorkStart()
    {
        $this->_server->on("workerStart",function (\swoole_server $server, $workerId)
        {
            define('SYSTEM_WORK_ID', $workerId);
            try
            {
                if ($this->_event) {
                    $this->_event->onWorkerStart($server,$workerId);
                }
            }
            catch (\Throwable $e)
            {
                $this->triggerThrowable($e);
            }


            if ($this->isWork($workerId)) {
                swoole_set_process_name('php-crontab');
                while(true)
                {
                    // zhe li hou xu hui tian jia du zan suo ji zhi yi ji dong tai geng xin ren wu gong neng
                    try{
                        foreach(Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'crontab')->run() as $task_item) {
                            if (!empty($task_item)) {
                                $this->addTask($task_item, -1);
                            }
                        }
                        // 每秒执行一次
                        usleep($this->getValueFromConf('task_step', 1000000));
                    } catch (Throwable $e) {
                        $this->triggerThrowable($e);
                        break;
                    }
                    
                }
            } else {
                swoole_set_process_name('php-task-crontab');
            }
        });
    }

    public function onRecive ()
    {
        $this->_server->on('receive', function ($serv, $fd, $from_id, $data) {
            return false;
        });
    }
}