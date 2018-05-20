<?php

namespace framework\client;
use framework\base\Base;

class BaseClient extends Base
{
  protected $_client;
  protected $_masterId;

  protected function init()
  {
    $this->onConnect();
    $this->onReceive();
    $this->onClose();
    $this->onError();
  }

  protected function afterConnect(\swoole_client $cl)
  {
    return true;
  }

  protected function onConnect()
  {
    $this->_client->on('connect', function (\swoole_client $cl) {
      $this->_masterId = posix_getpid();
      $this->afterConnect($cl);
    });
  }

  protected function afterReceive(\swoole_client $cl, $data)
  {
    return true;
  }

  protected function onReceive()
  {
    $this->_client->on('receive', function (\swoole_client $cli, $data) {
      $this->afterReceive($cli, $data);
    });
  }

  protected function afterClose(\swoole_client $cl)
  {
    return true;
  }

  protected function onClose()
  {
    $this->_client->on('close', function (\swoole_client $cli) {
      if ($this->_masterId == posix_getpid()) {
        $this->afterClose($cli);
      }
    });
  }

  protected function afterError(\swoole_client $cl)
  {
    return true;
  }

  protected function onError()
  {
    $this->_client->on('error', function (\swoole_client $cli) {
      $this->afterError($cli);
    });

  }

  public function connect()
  {
    $this->_client->connect($this->_conf['host'], $this->_conf['port']);
  }

  public function send($data)
  {
    $this->_client->send($data);
  }

  public function close()
  {
    $this->_client->close();
  }
}