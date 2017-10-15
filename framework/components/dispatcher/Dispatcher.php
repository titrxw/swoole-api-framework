<?php
namespace framework\components\dispatcher;
use framework\base\Component;
use framework\base\Container;

class Dispatcher extends Component
{
    protected $_controllerPrefix = null;
    protected $_controllerSuffix = null;
    protected $_actionPrefix = null;
    protected $_actionSuffix = null;

    public function run($args = array())
    {
        $controllerName = $this->getControllerPrefix() . $args['controller'] . $this->getControllerSuffix();
        $controllerName = ucfirst($controllerName);
        if (!file_exists(APP_ROOT.APP_NAME.'/controller/'.$controllerName.'.php'))
        {
            throw new \Exception(APP_ROOT.APP_NAME.'/controller/'.$controllerName.'.phpnot exists', 404);
        }

        $controllerHashName = md5(APP_NAME.'application/controller/'.$controllerName);

        Container::getInstance()->addComponent($controllerHashName,
            'application\\controller\\'. $controllerName);

        $actionName = $this->getActionPrefix() . $args['action'] . $this->getActionSuffix();
        $controllerInstance = $this->getComponent($controllerHashName);
        $controllerInstance->setController($controllerName);
        $controllerInstance->setAction($actionName);

        $result = $controllerInstance->beforeAction();
        if ($result !== true)
        {
            return $result;
        }
        if (!method_exists($controllerInstance, $actionName))
        {
            throw new \Exception('action ' . $actionName . ' not found');
        }
        $result = $controllerInstance->$actionName();
        $result = $controllerInstance->afterAction($result);
        unset($controllerInstance, $args);
        return $result;
    }

    protected function getControllerPrefix()
    {
        if(!isset($this->_controllerPrefix))
        {
            $this->_controllerPrefix = $this->getValueFromConf('controller.prefix');
        }
        return $this->_controllerPrefix;
    }

    protected function getControllerSuffix()
    {
        if(!isset($this->_controllerSuffix))
        {
            $this->_controllerSuffix = $this->getValueFromConf('controller.suffix');
        }
        return $this->_controllerSuffix;
    }

    protected function getActionPrefix()
    {
        if(!isset($this->_actionPrefix))
        {
            $this->_actionPrefix = $this->getValueFromConf('action.prefix');
        }
        return $this->_actionPrefix;
    }

    protected function getActionSuffix()
    {
        if(!isset($this->_actionSuffix))
        {
            $this->_actionSuffix = $this->getValueFromConf('action.suffix');
        }
        return $this->_actionSuffix;
    }
}