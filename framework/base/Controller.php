<?php
namespace framework\base;

abstract class Controller extends Component
{
    protected $_dbHandle;
    protected $_controller;
    protected $_action;
    protected $_requestController;
    protected $_requestAction;
    protected $_view;
    protected $_version;


    protected function init()
    {
        $this->unInstall(true);
    }

    public function before()
    {
        return true;
    }

    public function after($data = '')
    {
        return $data;
    }

    public function setVersion($version)
    {
        $this->_version = $version;
    }

    public function setRequestController($currentController)
    {
        $this->_requestController = $currentController;
    }

    public function setController($currentController)
    {
        $this->_controller = $currentController;
    }

    public function getController()
    {
        return $this->_controller;
    }

    public function getRequestController()
    {
        return $this->_requestController;
    }

    public function setRequestAction($action)
    {
        $this->_requestAction = $action;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function getRequestAction()
    {
        return $this->_requestAction;
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