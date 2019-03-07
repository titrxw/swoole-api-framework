<?php
namespace framework\components\zookeeper;

use framework\base\Conf;

class ZookeeperConf extends Conf
{
  protected $_watchNode;
  protected $_zconf ;
  protected $_nodes = [];
  protected $_zookeeper;


  protected function init()
  {
    $this->_zconf = new \swoole_table(2048);
    $this->_zconf->create();
    $this->_watchNode = $this->getValueFromConf('watch_node', []);
  }

  public function getHandle ()
  {
    if (!$this->_zookeeper) {
      $this->_zookeeper = new \framework\components\zookeeper\Zookeeper($this->getValueFromConf('hosts'),$this->getValueFromConf('name'),$this->getValueFromConf('password'));
    }

    return $this->_zookeeper;
  }

  public function get ($name)
  {
    $conf = $this->_zconf->get($this->_haver);
    if ($conf) {
      // 这里是给使用这用  所以这里的haver一定是使用者自己
      $this->_conf[$this->_haver] = $this->_zconf->get($this->_haver);
    }
    
    return parent::get($name);
  }

  public function watch()
  {
    $this->getHandle();
    foreach ($this->_watchNode as $key => $value) {
      # code...
      $this->_zookeeper->watch($value['node'], [$this, 'watchCallback']);
      $this->_nodes[] = $value['node'];
    }
  }

  public function watchCallback($event_type, $stat, $path)
  {
    try{
      $data = $this->_zookeeper->get($path);
      if ($data) {
        $data = \json_decode($data, true);
        $this->updateConf($data['path'], $data['version'],$path, array_search($path, $this->_nodes));
      }
    } catch (\Throwable $exception) {
        $this->handleThrowable($exception);
    }
  }

  protected function updateConf($path, $version, $node, $index)
  {
    $haver = $this->_watchNode[$index]['haver'];
    if ($haver == SYSTEM_APP_NAME) {
      $haver = 'framework/conf/';
    } else {
      $haver .= '/conf/';
    }
    $savePath = APP_ROOT . $haver . $this->_watchNode[$index]['save_path'];

    // 备份
    if (\file_exists($savePath)) {
      $oldPath = APP_ROOT . $haver . $version . '_' . $this->_watchNode[$index]['save_path'];
      \rename($savePath, $oldPath);
    }

    $src = fopen($path, 'rb');
    if ($src) {
      $dest = fopen($savePath, 'w');
      stream_copy_to_stream($src, $dest);
      fclose($dest);
      fclose($src);

      
      $dest = fopen($savePath, 'r');
      if ($dest) {
        $data = \stream_get_contents($dest);
        if ($data) {
          $data = \ltrim($data, '<?php');
          $data = \rtrim($data, '?>');
          $data = eval((string)$data);
          $pathinfo = \pathinfo($this->_watchNode[$index]['save_path']);
          $oconf = $this->_zconf->get($this->_watchNode[$index]['haver']);
          if (!$oconf) {
            $oconf = [];
          }
          $oconf[$pathinfo['filename']] = $data;
          $this->_zconf->set($this->_watchNode[$index]['haver'], $oconf);
          echo 'update success';
          return true;
        }
        fclose($dest);
      }
      $this->triggerThrowable(new \Exception('zookeeper conf file write to  path :' . $savePath . ' faile with node: ' . $node . ' and with version:' . $version));
    } else {
      $this->triggerThrowable(new \Exception('zookeeper conf update failed with path :' . $path . ' and node: ' . $node . ' and with version:' . $version));
    }
  }

  public function __destruct()
  {
    foreach ($this->_nodes as $key => $value) {
      # code...
      $this->_zookeeper->cancelWatch($value);
    }
  }
} 
