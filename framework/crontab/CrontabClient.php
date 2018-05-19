<?php

namespace framework\crontab;
use framework\client\TcpClient;
use framework\process\Manager;

class CrontabClient extends TcpClient
{
  protected $_processManager;
  protected $_busys = 0;

  protected function init()
  {
    parent::init();
  }

  protected function initTaskWork()
  {
    $num = $this->getValueFromConf('task_work_num',2);
    while($num > 0) {
      $this->_processManager->addProcess(new CrontabProcess());
      --$num;
    }
    $this->_processManager->start();
  }

  protected function afterConnect(\swoole_client $cl)
  {
    // 创建进程任务
    $this->_processManager = new Manager();
    $this->initTaskWork();
    if ($this->_processManager->getProcessNum() == 0) {
      $this->close();
      return false;
    }
    return parent::afterConnect($cl);
  }

  protected function afterReceive(\swoole_client $cl, $data)
  {
    $data = \json_decode($data, true);
    if (!empty($data['cmd']) && $data['cmd'] == 'task') {
      $num = 0;
      unset($data['cmd']);
      foreach ($this->_processManager->getAllProcess() as $value) {
        # code...
        if (!$value->isBusy()) {
          $value->toBusy();
          $value->write(json_encode($data['data']));
          ++$num;
          $this->checkBusy($num);
          break;
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