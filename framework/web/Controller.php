<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:11
 */
namespace framework\web;

use framework\base\Container;

abstract class Controller extends \framework\base\Controller
{
    protected $_magicRules = [
        'url',
        'request',
        'redis',
        'page',
        'response',
        'captcha'
    ];

    protected function rule()
    {
        return [];
    }

    protected function model($name)
    {
        $name = ucfirst($name);
        $componentModel = md5($this->getSystem() .'/controller/'.$name);
        Container::getInstance()->addComponent($this->getSystem(), $componentModel,
            $this->getSystem() .'\\model\\'. $name, Container::getInstance()->getComponentConf($this->getSystem(), 'model'));
//        在add之前设置当前model的conf
//        待开发
        return $this->getComponent($this->getSystem(), $componentModel);
    }

//    需要重写
    protected function validate()
    {
        $rule = $this->rule();
        if (empty($rule[$this->_action]))
        {
            unset($rule);
            return true;
        }
        $data = array('get' => $this->get(),'post' => $this->post());
        $result = $this->getComponent($this->getSystem(), 'validate')->run($data, $rule[$this->_action]);
        unset($rule, $data);
        return $result;
    }


    protected function assign($key, $value = null)
    {
        $this->getComponent($this->getSystem(), 'view')->assign($key, $value);
    }

    protected function display($path = '')
    {
        return $this->getComponent($this->getSystem(), 'view')->display($path);
    }

    protected function sendFile($path, $type = 'jpg')
    {
        if (file_exists(!$path))
        {
            return false;
        }
        $urlComponent = $this->getComponent(SYSTEM_APP_NAME, 'response');
        $urlComponent->contentType($type);
        $urlComponent->sendFile($path);
        unset($urlComponent);
        return true;
    }

    protected function addTask($className, $funcName, $params, $taskId = -1, $isAsync = false)
    {
        if (!$isAsync)
        {
            $this->getComponent($this->getSystem(), 'taskManager')->addTask($className, $funcName, $params, $taskId);
        }
        else
        {
            $this->getComponent($this->getSystem(), 'taskManager')->addAsyncTask($className, $funcName, $params, $taskId);
        }
    }

    public function addTimer($timeStep, callable $callable, $params= [])
    {
        return $this->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addTimer($timeStep, $callable, $params);
    }

    public function addTimerAfter($timeStep, callable $callable, $params= [])
    {
        return $this->getComponent(SYSTEM_APP_NAME, 'server')->getServer()->addTimerAfter($timeStep, $callable, $params);
    }
}