<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

use framework\base\Container;

/**
 * 需要利用zookeeper进行节点的管理，当节点退出的时候，把对应的service里包含的server踢出
 */
class ZookeeperRpcServer extends RpcServer
{
  protected $_zookeeper;

  protected function init()
  {
    parent::init();
    $this->_zookeeper = Container::getInstance()->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('conf', 'zookeeper'));
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

  /**
   * 目前没有处理一个service多个server的问题
   */
  protected function registerServices()
  {
    $services = $this->getValueFromConf('services', []);
    $root = $this->getValueFromConf('zookeeper_root', '');
    // $services = ['rxw' => 1];
    foreach ($services as $key => $value) {
      $key = '/' . ($root ? ($root . '/') : '') . $key;
      if (!$this->_zookeeper->getHandle()->exists($key)) {
        $this->_zookeeper->getHandle()->makeNode($key, null);
      }

		  $this->_zookeeper->getHandle()->makeNode($key . '/w-', json_encode([
        'host' => $this->_conf['ip'],
        'port' => $this->_conf['port']
      ]), [], \Zookeeper::EPHEMERAL | \Zookeeper::SEQUENCE);
    }
  }
}
