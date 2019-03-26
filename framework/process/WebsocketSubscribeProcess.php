<?php 
namespace framework\process;

use framework\base\Container;
use framework\components\mq\Mq;

class WebsocketSubscribeProcess extends Process
{
  protected $_mq;

  protected function afterInit()
  {
    $this->_handle->name('websocket-send-process');
  }

  protected function afterDoProcess(\swoole_process $worker)
  {
    $this->_mq = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'mq');
    $this->_mq->setHandle(function ($data) {

    });
    $this->_mq->start();
    return false;
  }

  protected function afterStop()
  {
    if ($this->_mq) {
      $this->_mq->stop();
    }
    return false;
  }
}
