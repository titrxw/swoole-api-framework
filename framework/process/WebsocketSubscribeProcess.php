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
    $this->_mq = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'distribute_ws_mq');
    $this->_mq->setQueue('websocket-send-msg-' . getMacAddr());
    $this->_mq->setHandle(function ($message) {
      $data = \json_decode($message->body, true);
      if ($this->_server->exist($data['fd'])) {
        $this->_server->push($data['fd'], $data['data'], true);
      }
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
