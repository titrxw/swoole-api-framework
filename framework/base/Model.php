<?php
namespace framework\base;

class Model extends Component
{
    protected $_sysMagicRules = [
        'url',
        'request',
        'page',
        'helper'
    ];
    protected $_appMagicRules = [
        'redis',
        'password',
    ];
    protected $_dbHandle;
    protected function init()
    {
        $this->unInstall();
    }

    public function db()
    {
        if (!$this->_dbHandle) {
            $this->_dbHandle = $this->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('db','meedo'));
        }
        return $this->_dbHandle;
    }

    /**
     * desc component 快捷获取方式
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (in_array($name, $this->_appMagicRules)) {
            $this->$name = $this->getComponent($this->getSystem(), $name);
            return $this->$name;
        }
        if (in_array($name, $this->_sysMagicRules)) {
            $this->$name = $this->getComponent(SYSTEM_APP_NAME, $name);
            return $this->$name;
        }
        return null;
    }
}