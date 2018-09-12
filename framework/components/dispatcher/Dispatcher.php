<?php
namespace framework\components\dispatcher;
use framework\base\Component;
use framework\base\Container;

class Dispatcher extends Component
{
    protected $_system;

    public function run($args = [])
    {
        $this->_system = \getModule();
        $args['controller'] = \ucfirst($args['controller']);
        $controllerName = $this->getValueFromConf('controller.prefix') . $args['controller'] . $this->getValueFromConf('controller.suffix');
        if (!\file_exists(APP_ROOT.$this->_system.'/controller/'.$controllerName.'.php'))
        {
            $this->triggerThrowable(new \Exception(APP_ROOT.$this->_system.'/controller/'.$controllerName.'.php not exists', 404));
        }

        $controllerHashName = \md5($this->_system.'/controller/'.$controllerName);

        Container::getInstance()->addComponent($this->_system, $controllerHashName,
            $this->_system.'\\controller\\'. $controllerName, Container::getInstance()->getComponentConf(\getModule(), 'controller'));

        $actionName = $this->getValueFromConf('action.prefix') . $args['action'] . $this->getValueFromConf('action.suffix');
        $controllerInstance = $this->getComponent(\getModule(), $controllerHashName);
        if (!\method_exists($controllerInstance, $actionName))
        {
            unset($controllerInstance, $args);
            $this->triggerThrowable(new \Exception('action ' . $actionName . ' not found'));
        }

        // 请求限制
        if ($this->getValueFromConf('route', false)) {
            $methods = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'doc')->parse($controllerInstance, $actionName)->getTags('method');
            if($methods) {
                $upMethod = \strtoupper($args['method']);
                $lowMethod = \strtolower($args['method']);
                if (!\in_array($lowMethod,$methods) && !\in_array($upMethod,$methods)) {
                    $this->triggerThrowable(new \Exception('action ' . $actionName . ' not found', 404));
                }
            }
        }

        $controllerInstance->setController($args['controller']);
        $controllerInstance->setAction($args['action']);


        $result = $controllerInstance->before();
        if ($result !== true)
        {
            unset($controllerInstance, $args);
            return $result;
        }

        $result = $controllerInstance->$actionName();

        $result = $controllerInstance->after($result);
        unset($controllerInstance, $args);
        return $result;
    }
}
