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
    protected function rule()
    {
        return [];
    }

    protected function model($name)
    {
        $name = ucfirst($name);
        $componentModel = md5(getModule() .'/controller/'.$name);
        Container::getInstance()->addComponent(getModule(), $componentModel,
            getModule() .'\\model\\'. $name, Container::getInstance()->getComponentConf(getModule(), 'model'));
//        在add之前设置当前model的conf
//        待开发
        return $this->getComponent(getModule(), $componentModel);
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
        $data = array('get' => $this->request->get(),'post' => $this->request->post());
        $result = $this->validate->run($data, $rule[$this->_action]);
        unset($rule, $data);
        return $result;
    }

    protected function assign($key, $value = null)
    {
        $this->view->assign($key, $value);
    }

    protected function display($path = '')
    {
        return $this->view->display($path);
    }

    protected function sendFile($path, $type = 'jpg')
    {
        if (!file_exists($path))
        {
            return false;
        }
        $this->response->contentType($type);
        $this->response->sendFile($path);
        return true;
    }

    protected function addTask($className, $funcName, $params,$taskId = -1, $isAsync = false)
    {
        if (!$isAsync)
        {
            $this->taskManager->addTask($className, $funcName, $params, $taskId);
        }
        else
        {
            $this->taskManager->addAsyncTask($className, $funcName, $params, $taskId);
        }
    }

    public function addTimer($timeStep, callable $callable, $params= [])
    {
        return $this->server->getServer()->addTimer($timeStep, $callable, $params);
    }

    public function addTimerAfter($timeStep, callable $callable, $params= [])
    {
        return $this->server->getServer()->addTimerAfter($timeStep, $callable, $params);
    }
}