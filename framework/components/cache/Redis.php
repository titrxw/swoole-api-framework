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
            $this->triggerException(new \Exception('not support: redis', 500));
        }
        unset($this->_conf);
        $this->_handle = new \Redis();

        $func = $this->getValueFromConf('persistent', false) === true ? 'pconnect' : 'connect';
        $timeout = $this->getValueFromConf('timeout', null);
        $this->_handle->$func($this->_appConf['host'], $this->_appConf['port'], $timeout);

        $password = $this->getValueFromConf('password');
        if ('' != $password) {
            if ($this->_handle->auth($password) === false) {
                $this->triggerException(new \Exception('redis auth password error', 500));
            }
        }

        $this->_appConf['select'] = $this->getValueFromConf('select', 0);
        if (0 != $this->_appConf['select']) {
            $this->_handle->select($this->_appConf['select']);
        }
    }

    public function selectDb(int $no)
    {
        if ($no == $this->_appConf['select']) {
            return false;
        }
        $this->_handle->select($no);
    }

    public function selectRollBack()
    {
        if (0 != $this->_appConf['select']) {
            $this->_handle->select($this->_appConf['select']);
        } else {
            $this->_handle->select(0);
        }
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
        if (false === $value) {
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

        /**
     * @param $redisKey
     * @param int $expire   redis key 生存事件
     * @param int $timeout  获取锁失败后的等待时间  微妙级
     * @param int $waitIntervalUs  获取锁失败后的每个多少时间再次获取锁  微妙级
     * @return bool|string
     */
    public function lock($redisKey, $expire = 500, $waitIntervalUs = 100000)
    {
        if (!$redisKey) {
            return false;
        }
        $retIdentifier = false;
        $identifier = md5(base64_encode(openssl_random_pseudo_bytes(32)) . microtime(true));
        /**
         * 说明  下面代码中的设置redis有效时间的作用暂时不明白
         */
        while (true) {
//            如果存在的话返回false
            if ($this->_handle->setnx($redisKey, $identifier) != false) {
                //如果成功后设置有效时间
                //这里的这个实在一个进程发生其他情况，导致还没执行玩就发生rediskey超时的情况下，该进程放弃了  那么这里就无法执行
                $this->_handle->pexpire($redisKey, $expire);
                //返回当前锁的所有者对于该锁的唯一标示符，  这样再释放的时候保证只有拥有者才能释放锁
                $retIdentifier = $identifier;
                break;
            }
            //因为上一步设置值和有效时间不是原子操作  可能再setnx后导致没有设置有效时间，这样的话因为只有锁的所有者才能释放锁，就会导致这个锁无法释放
            //当然这里不是锁的所有者执行，是抢锁的一方执行，避免锁无法释放
//            -1 表示key存在但是没设置时间
            if ($expire !== 0 && $this->_handle->pttl($redisKey) == -1) {
                $this->_handle->pexpire($redisKey, $expire);
            }
            //这里的重试是再一个进程抢占到锁后其他进程还要抢占锁的时候使用，如果有一个进程抢占了，其他进程不抢占的话可以不设置 也就是设置timeout为0 比如发短信
            //一微秒等于百万分之一秒。
            //隔 $waitIntervalUs 后继续 请求
            //这里添加随机因子是 当几个进程都抢不到锁的情况下，错开下次获取的时间， 避免不同客户端同时重试导致谁都无法拿到锁的情况出现
            //当然不添加也可以，添加后可增大一个进程获的锁的几率，不是再同时取锁，而是错开的取，几率大一点
            //假设都是每隔50毫秒取一次  那么下次就是第100毫秒再取，   但是如果加上随机树后第一个再50毫秒取，下一个再80毫秒取，如果第一个取到锁后再30秒内执行完成，然后让出锁，
            //那么当80毫秒的时候获取锁的时候又可以获取锁，几率比一起获取大
            $rand = mt_rand(0,$waitIntervalUs);
            usleep($waitIntervalUs + $rand);
        }
        return $retIdentifier;
    }
    public function unLock($redisKey, $identifier)
    {
        //保证释放者是锁的拥有者
        if ($identifier === $this->_handle->get($redisKey)) {
            $this->_handle->delete($redisKey);
            return true;
        }
        return false;
    }
}