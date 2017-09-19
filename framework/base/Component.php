<?php
namespace framework\base;

abstract class Component extends Base
{
    protected $_uniqueId = '';

    public function __construct($conf = array())
    {
        $this->_conf = empty($conf['default'])? array() : $conf['default'];
        $this->_appConf = empty($conf['app'])? array() : $conf['app'];

        unset($conf);
    }

    protected function getComponent($componentName,$params = array())
    {
        $params = func_get_args();
        array_shift($params);
        return Container::getInstance()->getComponent($componentName, $params);
    }

    protected function unInstall($isComplete = true)
    {
        Container::getInstance()->unInstall($this->_uniqueId, $isComplete);
    }

    public function setUniqueId($name)
    {
        $this->_uniqueId = $name;
        $this->init();
    }
}