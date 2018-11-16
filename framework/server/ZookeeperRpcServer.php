<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

/**
 * 需要利用zookeeper进行节点的管理，当节点退出的时候，把对应的service里包含的server踢出
 */
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

  public function serveChange($event_type, $stat, $path)
  {
    $workers = $this->_zookeeper->getHandle()->getChildren($path);     
    $size = count($workers);
    var_dump($workers);
    
    $servers = [];
		for ($i = 0; $i < $size; $i ++) {
          $res = $this->_zookeeper->getHandle()->get($path . '/' . $workers[$i - 1]);
          $res = json_decode($res, true);
          $servers[] = $res;
    }
    var_dump($servers);
    $this->_zookeeper->getHandle()->set($path, json_encode($servers));
  }

  /**
   * 目前没有处理一个service多个server的问题
   */
  protected function registerServices()
  {
    $services = $this->getValueFromConf('services', []);
    foreach ($services as $key => $value) {
      if (!$this->_zookeeper->getHandle()->exists($key)) {
        $this->_zookeeper->getHandle()->makeNode($key, null);
      }
      // 监控该节点的
      $this->_zookeeper->getHandle()->watch($key, [$this, 'serveChange']);
		  $this->znode = $this->_zookeeper->getHandle()->makeNode($key . '/w-', json_encode([
        'host' => $this->_conf['ip'],
        'port' => $this->_conf['port']
      ]), null, \Zookeeper::EPHEMERAL | \Zookeeper::SEQUENCE);
    }
  }
}
