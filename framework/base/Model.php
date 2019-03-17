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
    
    protected function model($name)
    {
        $name = \ucfirst($name);
        $module = \getModule();
        $componentModel = \md5($module .'/model/'.$name);
        Container::getInstance()->addComponent($module, $componentModel,
            $module .'\\model\\'. $name, Container::getInstance()->getComponentConf($module, 'model'));
//        在add之前设置当前model的conf
//        待开发
        return $this->getComponent($module, $componentModel);
    }

    public function db()
    {
        if (!$this->_dbHandle) {
            $this->_dbHandle = $this->getComponent(\getModule(), $this->getValueFromConf('db','meedo'));
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
        $module = \getModule();
        if (Container::getInstance()->hasComponent($module, $name)) {
            $this->$name = $this->getComponent($module, $name);
            return $this->$name;
        }
        if (Container::getInstance()->hasComponent(SYSTEM_APP_NAME, $name)) {
            $this->$name = $this->getComponent(SYSTEM_APP_NAME, $name);
            return $this->$name;
        }
        return null;
    }
}
