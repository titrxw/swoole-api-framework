<?php 
namespace framework\process;

use framework\base\Container;

class WebsocketSubscribeProcess extends Process
{
  protected function afterInit()
  {
    $this->_handle->name('websocket-send-process');
  }

  protected function afterDoProcess(\swoole_process $worker)
  {
    
    return false;
  }
}
