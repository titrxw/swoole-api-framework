<?php
namespace framework\components\request;
use framework\base\Component;

class Request extends Component
{
    protected $_method;
    protected $_rowBody;
    protected $_headers;
    protected $_hasCheckGet = false;
    protected $_hasCheckPost = false;
    protected $_hasCheckRequest = false;

    protected function init()
    {
        $this->unInstall(false);
    }

    protected function stripSlashes(&$data)
    {
        if(is_array($data))
        {
            if(count($data) == 0)
                return $data;
            $keys=array_map('stripslashes',array_keys($data));
            $data=array_combine($keys,array_values($data));
            return array_map(array($this,'stripSlashes'),$data);
        }
        else
            return stripslashes($data);
    }

    protected function checkGet()
    {
        if(!$this->_hasCheckGet)
        {
            if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
            {
                $_GET = $this->stripSlashes($_GET);
            }
            $this->_hasCheckGet = true;
        }
    }

    protected function checkPost()
    {
        if(!$this->_hasCheckPost)
        {
            if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
            {
                $_POST = $this->stripSlashes($_POST);
            }
            $this->_hasCheckPost = true;
        }
    }

    protected function checkRequest()
    {
        if(!$this->_hasCheckRequest)
        {
            if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
            {
                $_REQUEST = $this->stripSlashes($_REQUEST);
            }
            $this->_hasCheckRequest = true;
        }
    }

    public function getMethod()
    {
        if (empty($this->_method))
            $this->_method = $this->getServer()->getMethod();

        return $this->_method;
    }

    public function get($key = '', $default = '')
    {
        $this->checkGet();
        if(empty($key))
            return $_GET;
        if(!isset($_GET[$key]))
            return $default;
        else
            return $_GET[$key];
    }

    public function post($key = '', $default = '')
    {
        $this->checkPost();
        if(empty($key))
            return $_POST;
        if(!isset($_POST[$key]))
            return $default;
        else
            return $_POST[$key];
    }

    public function request($key = '', $default = '')
    {
        $this->checkRequest();
        if(empty($key))
            return $_REQUEST;
        if(!isset($_REQUEST[$key]))
            return $default;
        else
            return $_REQUEST[$key];
    }

    public function getRawBody()
    {
        if($this->_rowBody === null)
            $this->_rowBody=file_get_contents('php://input');
        return $this->_rowBody;
    }

    public function file($key)
    {
        return empty($_FILES[$key]) ? null : $_FILES[$key];
    }

    public function headers()
    {
        if(!empty($this->_headers))
            return $this->_headers;

        $tServuer = $this->getServer();
        foreach ($tServuer as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $this->_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        unset($tServuer);
        return $this->_headers;
    }

    public function header($key)
    {
        $server = $this->getServer();
        $result =  empty($server[$key]) ? '' : $server[$key];
        unset($server);
        return $result;
    }

    public function getServer()
    {
        return $this->getComponent('url')->getServer();
    }

    public function __destruct()
    {
        unset($_POST,$_GET,$_REQUEST,$_FILES);
    }
}