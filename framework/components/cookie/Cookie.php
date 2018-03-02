<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-8
 * Time: 下午9:28
 */
namespace framework\components\cookie;

use framework\base\Component;

class Cookie extends Component
{
    protected $_key;
    protected $_value;
    protected $_httpOnly;
    protected $_expire;
    protected $_path;
    protected $_domain;
    protected $_secure;

    protected $_cookies = [];

    protected function init()
    {
        $this->unInstall(false);
    }

    public function set($key, $value = '', $expire = 0 , $path = '/', $domain  = '', $secure = false , $httponly = false)
    {
        $params = func_get_args();
        array_shift($params);
        $this->_cookies[$key] = $params;
        unset($params);
    }

    public function send($else = '')
    {
        foreach ($this->_cookies as $key => $item)
        {
            setcookie($key, ...$item);
        }
        $this->rollback();
    }

    protected function rollback()
    {
        unset($this->_cookies);
        $this->_cookies = [];
    }

    public function get($key)
    {
        if (!$key) {
            return false;
        }
        return $_COOKIE[$key] ?? '';
    }
}