<?php
namespace framework\process;
use framework\base\Base;

class Manager extends Base
{
  protected $_process = [];
  protected $_hasWait;
  protected $_pids = [];

  public function addProcess(Process $process)
  {
    $this->_process[] = $process;
  }

  public function start()
  {
    foreach ($this->_process as $key => $value) {
      # code...
      if (!$value || !($value instanceof Process)) {
        $this->handleThrowable(new \Exception('cur process object is empty or not instanceof Process' . \serialize($value), 500));
        continue;
      }
      try{
        $pid =  $value->start();
      } catch (\Throwable $e) {
        $this->handleThrowable($e);
        unset($this->_process[$key]);
        continue;
      }
      
      if (isset($pid)) {
        $this->_pids[$pid] = $key;
      }
      \var_dump($this->_pids);
    }

    $this->wait();
  }

  protected function wait()
  {
    if ($this->_hasWait) {
      return false;
    }
    $this->_hasWait = true;
    $num = $this->getProcessNum();
    \swoole_process::signal(SIGCHLD, function($sig) use ($num)  {
      //必须为false，非阻塞模式
      static $killProcessNum = 0;
      while($ret =  \swoole_process::wait(false)) {
        $this->stopProcess($ret['pid']);
        ++$killProcessNum;
        if ($killProcessNum == $num) {
          // 删除事件循环
          \swoole_event_exit();
        }
      }
    });
  }

  protected function stopProcess($pid)
  {
    if (isset($this->_pids[$pid])) {
      unset($this->_process[$this->_pids[$pid]]);
      unset($this->_pids[$pid]);
      $this->handleThrowable(new \Exception('process ' . $pid .  ' stop '));
    }
  }

  public function getProcessNum()
  {
    return count($this->_process);
  }

  public function getAllProcess()
  {
    return $this->_process;
  }

  public function kill($pid = null)
  {
    if ($pid) {
      if (isset($this->_pids[$pid])) {
        $this->_process[$this->_pids[$pid]]->stop();
      }
      return true;
    }

    foreach ($this->_process as $key => $value) {
      # code...
      $value->stop();
    }
  }
}