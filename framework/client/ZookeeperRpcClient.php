<?php
namespace framework\client;

use framework\base\Component;

class ZookeeperRpcClient extends RpcClient
{
  protected function init()
  {
    $this->_zookeeper = $this->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('conf', 'zookeeper'));
  }

  public function getServer($path)
  {
    $workers = $this->_zookeeper->getHandle()->getChildren($path); 
    if (!$workers) {
      return false;
    } 
    $worker = mt_rand(0, \count($workers) - 1);
    $res = $this->_zookeeper->getHandle()->get($path . '/' . $worker);
    $res = json_decode($res, true);
    if (\is_array($res)) {
      return $res;
    }
    return false;
  }

  public function call($service, $method, ...$args)
  {
    // 根据service从zookeeper上获取对应的service的服务器地址
    $info = $this->getServer($service);
    if (!$info) {
      return [404];
    }

    return parent::call($info['host'], $info['port'], $service, $method, ...$args);
    
  }
}
