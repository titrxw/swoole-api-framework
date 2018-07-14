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
    parent::init();
    $this->_zconf = new \swoole_table(2048);
    \var_dump($this->_zconf);
    $this->_watchNode = $this->getValueFromConf('watch_node', [
      ['node' => '/config/' . $this->_haver, 'save_path' => 'main.php']
    ]);
  }

  public function get ($name)
  {
    \var_dump(getModule());
    \var_dump($this->_zconf);
    $conf = $this->_zconf->get(getModule());
    if ($conf) {
      $this->_conf[getModule()] = $this->_zconf->get(getModule());
    }
    
    return parent::get($name);
  }

  public function watch()
  {
    $this->_zookeeper = new \framework\components\zookeeper\Zookeeper($this->getValueFromConf('hosts', ''));
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
    \var_dump($node);
    $haver = $this->_haver;
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
      if (!$this->_supportYaf) {
        $dest = fopen($savePath, 'r');
        if ($dest) {
          $data = \stream_get_contents($dest);
          if ($data) {
            $data = \ltrim($data, '<?php');
            $data = \rtrim($data, '?>');
            $data = eval((string)$data);
            $pathinfo = \pathinfo($this->_watchNode[$index]['save_path']);
            $table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
            $oconf = $this->_zconf->get($this->_haver);
            if (!$oconf) {
              $oconf = [];
            }
            $oconf[$pathinfo['filename']] = $data;
            $this->_zconf->set($this->_haver, $oconf);
            echo 'update success';
            return true;
          }
          fclose($dest);
        }
        $this->triggerThrowable(new \Exception('zookeeper conf file write to  path :' . $savePath . ' faile with node: ' . $node . ' and with version:' . $version));
      }
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