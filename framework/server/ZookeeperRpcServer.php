<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

class ZookeeperRpcServer extends RpcServer
{
  protected $_process;
  protected $_zookeeper;

  protected function init()
  {
    parent::init();
    $this->_zookeeper = $this->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('conf', 'zookeeper'));
  }

  protected function afterStart(\swoole_server $server)
  {
    if ($this->getValueFromConf('mode') == SWOOLE_BASE) {
      $this->registerServices();
    }
    return true;
  }
  
  protected function afterManagerStart(\swoole_server $server)
  {
    $this->registerServices();
    return true;
  }

  protected function registerServices()
  {
    $services = $this->getValueFromConf('services', []);
    foreach ($services as $key => $value) {
      $this->_zookeeper->getHandle()->set($key, json_encode([
        'host' => $this->_conf['ip'],
        'port' => $this->_conf['port'],
        'status' => 200
      ]));
    }
  }
}