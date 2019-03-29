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

        $bconf = Container::getInstance()->getComponentConf(SYSTEM_APP_NAME, 'controller');
        $conf = Container::getInstance()->getComponentConf($this->_system, 'controller');
        $conf = array_merge($bconf, $conf);

        $controllerName = ($conf['controller']['prefix'] ?? '') . $args['controller'] . ($conf['controller']['suffix'] ?? '');
        $controllerPath = APP_ROOT.$this->_system.'/controller/'. $args['version'] . '/' .$controllerName.'.php';
        if (!\file_exists($controllerPath))
        {
            $this->triggerThrowable(new \Exception($controllerPath.' not exists', 404));
        }
        $controllerHashName = \md5($this->_system.'/controller/'. $args['version'] . '/' .$controllerName);

        $actionName = ($conf['action']['prefix'] ?? '') . $args['action'] . ($conf['action']['suffix'] ?? '');

        Container::getInstance()->addComponent($this->_system, $controllerHashName,
            $this->_system.'\\controller\\'. $controllerName,$conf);

        $controllerInstance = $this->getComponent($this->_system, $controllerHashName);
        if (!\method_exists($controllerInstance, $actionName))
        {
            unset($controllerInstance, $args);
            $this->triggerThrowable(new \Exception('action ' . $args['action'] . ' not found'));
        }

        // 请求限制
        if ($this->getValueFromConf('route_filter', false)) {
            $methods = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'doc')->parse($controllerInstance, $actionName)->getTags('method');
            if($methods) {
                $upMethod = \strtoupper($args['method']);
                $lowMethod = \strtolower($args['method']);
                if (!\in_array($lowMethod,$methods) && !\in_array($upMethod,$methods)) {
                    $this->triggerThrowable(new \Exception('action ' . $args['action'] . ' not found', 404));
                }
            }
        }
        
        $controllerInstance->setVersion($args['version']);
        $controllerInstance->setController($controllerName);
        $controllerInstance->setAction($actionName);
        $controllerInstance->setRequestController($args['controller']);
        $controllerInstance->setRequestAction($args['action']);

        $result = $controllerInstance->before();
        if ($result === true)
        {
            // 这里尝试把数据放到action的参数中   只放get的数据
            $_params = [];
            $params = new \ReflectionMethod($controllerInstance, $actionName);
            $params = $params->getParameters();
            $request = $this->getComponent(SYSTEM_APP_NAME, 'request');
            foreach($params as $item) {
                $val = $request->get($item->name);
                if (!$val && $item->isDefaultValueAvailable()) {
                    $val = $item->getDefaultValue ();
                }
                $_params[] = $val;
            }
            if ($_params) {
                $result = $controllerInstance->$actionName(...$_params);
            } else {
                $result = $controllerInstance->$actionName();
            }
        }

        $result = $controllerInstance->after($result);
        unset($controllerInstance, $args);
        return $result;
    }
}
