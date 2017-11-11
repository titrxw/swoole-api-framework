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
        return array();
    }
    /**
     * desc 获取请求参数中的get参数
     * @param $key
     * @param null $default
     * @return null
     */
    protected function get($key = null, $default = null)
    {
        return $this->getComponent('request')->get($key,$default);
    }

    /**desc  获取请求参数中的post参数
     * @param $key
     * @param null $default
     */
    protected function post($key = null, $default = null)
    {
        return $this->getComponent('request')->post($key,$default);
    }

    /**
     * 获取请求参数中的所有参数 包括get和post
     */
    protected function getRequestParams()
    {
        return $this->getComponent('request')->request();
    }

    protected function rediret($url)
    {
        $response = $this->getComponent('response');
        $response->addHeader('Location', $url);
        $response->setCode(302);
        unset($response);
        return '';
    }

    protected function isPost()
    {
        if ($this->getComponent('request')->getMethod() === 'post')
        {
            return true;
        }
        return false;
    }

    protected function createUrl($url)
    {
        $urlModule = $this->getComponent('url');
        $type = $urlModule->getType();
        $tmpUrl = $urlModule->getHost() . $urlModule->getUrl() . '?';
        if ($type === '?')
        {
            if(is_array($url))
            {
                foreach ($url as $key=>$item)
                {
                    $tmpUrl .= $key . '=' . $item . '&';
                }
                $tmpUrl = trim($tmpUrl, '&');
            }
            else
            {
                $tmpUrl .= $url;
            }
        }
        else
        {
            $tmpUrl .= $url;
        }

        unset($urlModule, $url);
        return $tmpUrl;
    }

    protected function ajax($data)
    {
        $urlComponent = $this->getComponent('response');
        $urlComponent->noCache();
        $urlComponent->contentType('json');
        unset($urlComponent);
        return $data;
    }

    protected function model($name)
    {
        $componentModel = md5(APP_NAME.'application/controller/'.$name);
        Container::getInstance()->addComponent($componentModel,
            'application\\model\\'. $name);
        return $this->getComponent($componentModel);
    }

    protected function validate()
    {
        $rule = $this->rule();
        if (empty($rule[$this->_action]))
        {
            unset($rule);
            return true;
        }
        $data = array('get' => $this->get(),'post' => $this->post());
        $result = $this->getComponent('validate')->run($data, $rule[$this->_action]);
        unset($rule, $data);
        return $result;
    }

    protected function isAjax()
    {
        $server = $this->getComponent('url')->getServer();
        $result = isset($server['HTTP_X_REQUESTED_WITH']) && $server['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
        unset($server);
        return $result;
    }

    protected function assign($key, $value = null)
    {
        $this->getComponent('view')->assign($key, $value);
    }

    protected function display($path = '')
    {
        return $this->getComponent('view')->display($path);
    }

    protected function getRedis()
    {
        return $this->getComponent('redis');
    }

    protected function getPage()
    {
        return $this->getComponent('page');
    }

    protected function sendFile($path, $type = 'jpg')
    {
        if (file_exists(!$path))
        {
            return false;
        }
        $urlComponent = $this->getComponent('response');
        $urlComponent->contentType($type);
        $urlComponent->sendFile($path);
        unset($urlComponent);
        return true;
    }

    protected function addTask($className, $funcName, $params, $taskId = -1, $isAsync = false)
    {
        if (!$isAsync)
        {
            $this->getComponent('taskManager')->addTask($className, $funcName, $params, $taskId);
        }
        else
        {
            $this->getComponent('taskManager')->addAsyncTask($className, $funcName, $params, $taskId);
        }
    }

    public function addTimer($timeStep, callable $callable, $params= array())
    {
        return $this->getComponent('server')->getServer()->addTimer($timeStep, $callable, $params);
    }

    public function addTimerAfter($timeStep, callable $callable, $params= array())
    {
        return $this->getComponent('server')->getServer()->addTimerAfter($timeStep, $callable, $params);
    }
}