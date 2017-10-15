<?php
namespace framework\base;

abstract class Controller extends Component
{
    protected $_controller;
    protected $_action;
    protected $_view;

    protected function init()
    {
        $this->unInstall();
    }

    public function beforeAction()
    {
        return true;
    }

    public function afterAction($data = '')
    {
        return $data;
    }

    protected function rule()
    {
        return array();
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

    /**
     * desc component 快捷获取方式
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        $func = 'get'.ucfirst($name);
        if (method_exists($this, $func))
        {
            return $this->$func();
        }
        return null;
    }
}