<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace framework\components\session\driver;

use framework\base\Container;

class Redis extends \SessionHandler
{
    protected $_handler = null;
    protected $_conf  = [];

    public function __construct($config = [])
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('not support: redis', 500);
        }
        if (empty($config['_name'])) {
            $config['_name'] = 'redis';
        }
        $this->_conf = $config;
        Container::getInstance()->getComponent($this->_conf['_name']);
        unset($config);
    }

    /**
     * 打开Session
     * @access public
     * @param string $savePath
     * @param mixed  $sessName
     * @return bool
     * @throws Exception
     */
    public function open($savePath, $sessName)
    {
        try
        {
            $this->_handler = Container::getInstance()->getComponent($this->_conf['_name']);
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage(),500);
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        unset($this->_handler);
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     * @return string
     */
    public function read($sessID)
    {
        return (string) $this->_handler->get($this->_conf['session_name'] . $sessID);
    }

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param String $sessData
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        return true;
//        return $this->_handler->set($this->_conf['session_name'] . $sessID, $sessData);
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID)
    {
//        $this->_handler->rm($this->_conf['session_name'] . $sessID);
        return true;
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     * @return bool
     */
    public function gc($sessMaxLifeTime)
    {
        return true;
    }
}
