<?php

namespace framework\crontab;
use framework\client\TcpClient;

class CrontabClient extends TcpClient
{
  protected function init()
  {
    parent::init();
  }

  protected function afterReceive(\swoole_client $cl, $data)
  {
    \var_dump($data);
    return parent::afterReceive($cl, $data);
  }
}