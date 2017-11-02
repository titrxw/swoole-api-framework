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

class Redis extends Cache implements CacheInterface
{
    protected function init()
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('not support: redis', 500);
        }
        unset($this->_conf);
        $this->_handle = new \Redis();

        $func = $this->getValueFromConf('persistent', false) === true ? 'pconnect' : 'connect';
        $timeout = $this->getValueFromConf('timeout', null);
        $this->_handle->$func($this->_appConf['host'], $this->_appConf['port'], $timeout);

        $password = $this->getValueFromConf('password');
        if ('' != $password) {
            if ($this->_handle->auth($password) === false) {
                throw new \Exception('redis auth password error', 500);
            }
        }

        if (0 != $this->_appConf['select']) {
            $this->_handle->select($this->_appConf['select']);
        }
    }

    public function selectDb(int $no)
    {
        if ($no == 0) {
            return false;
        }
        $this->_handle->select($no);
    }

    public function selectRollBack()
    {
        $this->_handle->select(0);
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->_handle->exists($this->getCacheKey($name)) ? true : false;
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
        if (false !== $value) {
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
}
