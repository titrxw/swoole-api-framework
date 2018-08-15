<?php 
namespace framework\process;


class AutoReloadProcess extends Process
{
  protected $pid;


  protected function afterInit()
  {
    $this->_handle->name('reload-process');
  }

  public function setServerPid($pid)
  {
    $this->pid = $pid;
  }

  public function doProcess(\swoole_process $worker)
  {
    $kit = new \framework\autoreload\AutoReload($this->pid);  
    $kit->watch(APP_ROOT);
    $kit->run();
  }
}