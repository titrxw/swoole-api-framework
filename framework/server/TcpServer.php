<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

use framework\base\Container;

class TcpServer extends BaseServer
{
    protected function init()
    {
        if (!$this->_server) {
          $this->_server = new \swoole_server($this->_conf['ip'], $this->_conf['port']);
        }

        parent::init(); // TODO: Change the autogenerated stub
        $this->onReceive();
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

    public function send($fd, $data, $eof = '')
    {
      $this->_server->send($fd, $data . $eof);
    }
}