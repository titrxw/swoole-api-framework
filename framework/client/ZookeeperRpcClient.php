<?php
namespace framework\client;

use framework\base\Component;

class ZookeeperRpcClient extends RpcClient
{
  protected function init()
  {
    $this->_zookeeper = $this->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('conf', 'zookeeper'));
  }

  public function call($service, $method, ...$args)
  {
    // 根据service从zookeeper上获取对应的service的服务器地址
    $info = $this->_zookeeper->getHandle()->get($service);
    if (!$info) {
      return [404];
    }
    $info = json_encode($info, true);
    if ($info['status'] == 300) {
      return [300];
    }

    return parent::call($info['host'], $info['port'], $service, $method, ...$args);
    
  }
}
