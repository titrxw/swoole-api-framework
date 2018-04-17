<?php
namespace framework\base;
use framework\traits\Common;

abstract class Component extends Base
{
    use Common;
    protected $_uniqueId = '';

    public function __construct($conf = [])
    {
        $this->_conf = $conf['default'] ?? [];
        $this->_appConf = $conf['app'] ?? [];

        unset($conf);
    }

    final protected function getComponent($haver, $componentName,$params = [])
    {
        $params = func_get_args();
        array_shift($params);
        return Container::getInstance()->getComponent($haver, $componentName, $params);
    }

    final protected function unInstall($isComplete = false)
    {
        Container::getInstance()->unInstall($this->getSystem(), $this->_uniqueId, $isComplete);
    }

    final protected function unInstallNow($isComplete = false)
    {
        if ($isComplete) {
            Container::getInstance()->destroyComponentsInstance($this->getSystem(), $this->_uniqueId);
        } else {
            Container::getInstance()->destroyComponent($this->getSystem(), $this->_uniqueId);
        }
    }

    final public function getConfPack ()
    {
        return [
            'default' => $this->_conf,
            'app' => $this->_appConf
        ];
    }

    final public function setUniqueId($name)
    {
        $this->_uniqueId = $name;
        $this->init();
    }
}