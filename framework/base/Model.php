<?php
namespace framework\base;

class Model extends Component
{
    protected $_dbHandle;
    
    protected function afterInit()
    {
    }

    protected function init()
    {
        $this->unInstall(true);
        $this->afterInit();
    }

    public function db()
    {
        if (!$this->_dbHandle) {
            $this->_dbHandle = $this->getComponent(getModule(), $this->getValueFromConf('db','meedo'));
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
        if (Container::getInstance()->hasComponent(getModule(), $name)) {
            $this->$name = $this->getComponent(getModule(), $name);
            return $this->$name;
        }
        if (Container::getInstance()->hasComponent(SYSTEM_APP_NAME, $name)) {
            $this->$name = $this->getComponent(SYSTEM_APP_NAME, $name);
            return $this->$name;
        }
        return null;
    }
}
