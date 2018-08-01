<?php

namespace framework\process;
use framework\base\Base;


class Process extends Base
{
  protected $_handle;
  protected $_hasStart;
  protected $_sureStop = false;
  protected $_pid;

  protected function afterInit()
  {
    $this->_handle->name('process');
  }

  protected function init()
  {
    $this->_handle = new \swoole_process([$this,'doProcess'], false, 2);
    $this->afterInit();
  }

  protected function afterDoProcess(\swoole_process $worker)
  {
    return false;
  }

  protected function doProcessMsg(\swoole_process $worker, $data = '')
  {
    return false;
  }

  public function doProcess(\swoole_process $worker)
  {
    try{
      swoole_event_add($worker->pipe, function($pipe) use ($worker) {
        $data = $worker->read();
        if ($data) {
          if ($data === 'stop') {
            $this->_sureStop = true;
            $worker->write('stop');
            swoole_event_del($pipe);
          } else {
            try{
              $this->doProcessMsg($worker, $data);
            } catch (\Throwable $e) {
              $this->handleThrowable($e);
            }
          }
        }
      });
      $this->afterDoProcess($worker);
    } catch (\Throwable $e) {
      $this->handleThrowable($e);
    }
  }

  protected function doChildMsg($msg)
  {
    return true;
  }

  protected function readChildMsg()
  {
    swoole_event_add($this->_handle->pipe, function($pipe) {
        $recv = $this->_handle->read();
        try{
          if ($recv === 'stop') {
            swoole_event_del($pipe);
            return false;
          }
          $this->doChildMsg($recv);
        } catch (\Throwable $e) {
          $this->handleThrowable($e);
        }
    });
  }

  public function getPid()
  {
    $this->checkProcess();
    return $this->_pid;
  }

  protected function checkProcess()
  {
    if (!$this->_handle) {
      $this->triggerThrowable(new \Exception('process instance is null ,please init it before start',  500));
    }
  }

  public function write($data)
  {
    $this->checkProcess();
    $this->_handle->write($data);
  }

  public function start()
  {
    if ($this->_hasStart) {
      return false;
    }
    $this->checkProcess();

    $this->_pid = $this->_handle->start();
    $this->readChildMsg();
    $this->_hasStart = true;
    
    return $this->_pid;
  }

  public function stop()
  {
    if ($this->_hasStart) {
      $this->_hasStart = false;
      $this->checkProcess();
      $this->_handle->write('stop');
    }
  }
}