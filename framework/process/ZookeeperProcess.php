<?php 
namespace framework\process;

use framework\base\Container;

class ZookeeperProcess extends Process
{
  protected function afterInit()
  {
    $this->_handle->name('zookeeper-process');
  }

  protected function afterDoProcess(\swoole_process $worker)
  {
    Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'zookeeper')->watch();
    while(!$this->_sureStop) {
      sleep(1);
    }
    return false;
  }
}