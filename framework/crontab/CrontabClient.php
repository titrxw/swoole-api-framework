<?php

namespace framework\crontab;
use framework\client\TcpClient;
use framework\process\Manager;

class CrontabClient extends TcpClient
{
  protected $_processManager;

  protected function init()
  {
    parent::init();

    // 由于process 采用的面向对象的方式  那么当进程kill的时候要销毁所有的变量 也就会销毁CrontabClient的实例，就会触发swoole client的 destruct   会导致和server断开连接
    // 放在这里的话  start 进程的时候 此时的this 还没有和server进行连接  ，这样的话所有的和server的连接控制在主进程
    $this->_processManager = new Manager();
    $this->initTaskWork();
    if ($this->_processManager->getProcessNum() == 0) {
      $this->handleThrowable(new Exception('client create process failed'));
      exit();
      return false;
    }
  }

  protected function initTaskWork()
  {
    $num = $this->getValueFromConf('task_work_num',3);
    while($num > 0) {
      $this->_processManager->addProcess(new CrontabProcess());
      --$num;
    }
    $this->_processManager->start();
  }

  protected function afterConnect(\swoole_client $cl)
  {
    // 创建进程任务
    $this->send('doer');
    return parent::afterConnect($cl);
  }

  protected function afterReceive(\swoole_client $cl, $data)
  {
    $data = \explode('\n\r', $data);
    foreach ($data as $cmd) {
      # code...
      if (!$cmd) {
        continue;
      }
      $cmd = \json_decode($cmd, true);
      if (!empty($cmd['cmd']) && $cmd['cmd'] == 'task') {
        $num = 0;
        unset($cmd['cmd']);
        foreach ($this->_processManager->getAllProcess() as $value) {
          # code...
          if (!$value->isBusy()) {
            $value->toBusy();
            $value->write(json_encode($cmd['data']));
            ++$num;
            $this->checkBusy($num);
            break;
          }
        }
      }
    }
    return parent::afterReceive($cl, $data);
  }

  protected function checkBusy($num)
  {
    if ($num == $this->_processManager->getProcessNum()) {
      $this->send('busy');
    }
  }
  
  protected function afterClose(\swoole_client $cl)
  {
    $this->_processManager->kill();
    return parent::afterClose($cl);
  }

  protected function afterError(\swoole_client $cl)
  {
    $this->_processManager->kill();
    return parent::afterError($cl);
  }
}