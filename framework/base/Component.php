<?php
namespace framework\base;

abstract class Component extends Base
{
    protected $_uniqueId = '';

    public function __construct($conf = [])
    {
        $this->_conf = $conf['default'] ?? [];
        $this->_appConf = $conf['app'] ?? [];

        unset($conf);
    }

    protected function getComponent($haver, $componentName,$params = [])
    {
        $params = func_get_args();
        array_shift($params);
        return Container::getInstance()->getComponent($haver, $componentName, $params);
    }

    protected function unInstall($isComplete = true)
    {
        Container::getInstance()->unInstall($this->getSystem(), $this->_uniqueId, $isComplete);
    }

    public function setUniqueId($name)
    {
        $this->_uniqueId = $name;
        $this->init();
    }

    public function getSystem()
    {
        return Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'dispatcher')->getSystem();
    }
}