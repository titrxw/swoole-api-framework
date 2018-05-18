<?php

namespace framework\client;

class TcpClient extends BaseClient
{
  protected $_client;
  protected function init()
  {
    $this->_client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
    parent::init();
  }
}