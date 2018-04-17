<?php
namespace framework\base;

abstract class Controller extends Component
{
    protected $_controller;
    protected $_action;
    protected $_view;

    protected $_sysMagicRules = [
        'url',
        'request',
        'response',
        'conf',
        'helper'
    ];
    protected $_appMagicRules = [
        'redis',
    ];

    protected function init()
    {
        $this->unInstall(true);
    }

    public function beforeAction()
    {
        return true;
    }

    public function afterAction($data = '')
    {
        return $data;
    }

    public function setController($currentController)
    {
        $this->_controller = $currentController;
    }

    public function getController()
    {
        return $this->_controller;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    public function getAction()
    {
        return $this->_action;
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
        if (Container::getInstance()->hasComponent($this->getSystem(), $name)) {
            $this->$name = $this->getComponent($this->getSystem(), $name);
            return $this->$name;
        }
        if (Container::getInstance()->hasComponent(SYSTEM_APP_NAME, $name)) {
            $this->$name = $this->getComponent(SYSTEM_APP_NAME, $name);
            return $this->$name;
        }
        return null;
    }
}