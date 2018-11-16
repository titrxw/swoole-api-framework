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
    $info = json_decode($info, true);
    // 这里是数组
    $info = $info[mt_rand(0, \count($info) - 1)];

    return parent::call($info['host'], $info['port'], $service, $method, ...$args);
    
  }
}
