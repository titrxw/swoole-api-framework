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

    final protected function getComponent($haver, $componentName,$params = [])
    {
        $params = func_get_args();
        array_shift($params);
        return Container::getInstance()->getComponent($haver, $componentName, $params);
    }

    final protected function unInstall($isComplete = false)
    {
        Container::getInstance()->unInstall(getModule(), $this->_uniqueId, $isComplete);
    }

    final public function unInstallNow($isComplete = false)
    {
        if ($isComplete) {
            Container::getInstance()->destroyComponent(getModule(), $this->_uniqueId);
        } else {
            Container::getInstance()->destroyComponentsInstance(getModule(), $this->_uniqueId);
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


    public function __destruct()
    {
        \var_dump($this->_uniqueId);
        $this->handleThrowable(new \Exception($this->_uniqueId));
        // TODO: Implement __destruct() method.
    }
}