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
    $zookeeper = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'zookeeper');
    $zookeeper->watch();
    while(!$this->_sureStop) {
      sleep(1);

      $state = $zookeeper->getState();
      if ($state == \Zookeeper::EXPIRED_SESSION_STATE || $state == \Zookeeper::NOTCONNECTED_STATE) {
        Container::getInstance()->unInstall(SYSTEM_APP_NAME, 'zookeeper');
        $zookeeper = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'zookeeper');
        $zookeeper->watch();
      }
    }
    return false;
  }
}
