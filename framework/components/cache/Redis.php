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

namespace framework\components\cache;
use framework\base\Component;

class Redis extends Component implements CacheInterface
{
    protected $_handle = null;

    protected function init()
    {
        if (!extension_loaded('redis')) {
            throw new \Error('not support: redis');
        }
        unset($this->_conf);
        $func = $this->_appConf['persistent'] ? 'pconnect' : 'connect';
        $this->_handle = new \Redis;
        $this->_handle->$func($this->_appConf['host'], $this->_appConf['port'], $this->_appConf['timeout']);

        if ('' != $this->_appConf['password']) {
            $this->_handle->auth($this->_appConf['password']);
        }

        if (0 != $this->_appConf['select']) {
            $this->_handle->select($this->_appConf['select']);
        }
    }

    public function getCacheKey($name)
    {
        return (empty($this->_appConf['prefix'])?'':$this->_appConf['prefix']) . $name;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->_handle->get($this->getCacheKey($name)) ? true : false;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = $this->_handle->get($this->getCacheKey($name));
        if (is_null($value)) {
            return $default;
        }

        $jsonData = json_decode($value, true);
        return (null === $jsonData) ? $value : $jsonData;
    }

    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param integer   $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->_appConf['expire'];
        }

        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->_handle->setex($this->getCacheKey($name), $expire, $value);
        } else {
            $result = $this->_handle->set($this->getCacheKey($name), $value);
        }

        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        return $this->_handle->incrby($this->getCacheKey($name), $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        return $this->_handle->decrby($this->getCacheKey($name), $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return $this->_handle->delete($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear()
    {
        return $this->_handle->flushDB();
    }

    public function __destruct()
    {
        if(isset($this->_appConf['persistent']) && $this->_appConf['persistent'] === false)
        {
            $this->_handle->close();
            $this->_handle = null;
        }
    }
}
