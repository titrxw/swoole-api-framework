<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-3-25
 * Time: 下午3:20
 */
namespace framework\tokenbucket;
use framework\base\Base;
use framework\components\cache\Cache;

abstract class TokenBucket extends Base
{
    protected $_bucketListKey = 'bucket_token_redis_list';
    protected $_key;
    protected $_max;
    protected $_addStep = 0;
    protected $_timeStep = 1000;
    protected $_range;
    protected $_storeHandle = null;
    protected $_system;

    protected function init()
    {
        $this->_key = $this->getValueFromConf('key', '');
        $this->_max = $this->getValueFromConf('max', 0);
        $this->_addStep = $this->getValueFromConf('addStep', 0);
        $this->_timeStep = $this->getValueFromConf('timeStep', 0);
        $this->_range = $this->getValueFromConf('range', 0);
        $this->_range /= 1000;
    }

    public function setSystem($system)
    {
        $this->_system = $system;
    }

    abstract public function run(\framework\components\request\Request $request, $data = []);

    public function setStoreHandle(Cache $redis)
    {
        $this->_storeHandle = $redis;
    }

    final protected function check()
    {
        if (!$this->_storeHandle) {
            $this->triggerException(new \Exception('token bucket store can not be null', 500));
        }
        if (!$this->_key) {
            $this->triggerException(new \Exception('token bucket key can not be null', 500));
        }
        if ($this->_max <= 0) {
            $this->triggerException(new \Exception('token bucket max mus be greater than 0', 500));
        }
        if ($this->_range <= 0) {
            $this->triggerException(new \Exception('token bucket range mus be greater than 0', 500));
        }

        $key = $this->_system . $this->_key;
        $retIdentifier = $this->_storeHandle->lock($key);
        if (!$this->_storeHandle->has($key)) {
            $cur = $this->_max - 1;
            $this->_storeHandle->set($key, ['cur' => $cur, 'last' => time()], $this->_range);
            $this->_storeHandle->getHandle()->lpush($this->_system . $this->_bucketListKey, $key);
            if ($cur < 0) {
                $this->_storeHandle->unLock($key, $retIdentifier);
                return false;
            }
        } else {
            $data = $this->_storeHandle->get($key);
            $add = floor(((time() - $data['last']) / $this->_timeStep) * $this->_addStep);
            $cur = $data['cur'] + $add;
            --$cur;
            if ($cur < 0) {
                $this->_storeHandle->unLock($key, $retIdentifier);
                return false;
            }

            $this->_storeHandle->set($key, ['cur' => $cur, 'last' => time()], $this->_range);
        }

        $this->_storeHandle->unLock($key, $retIdentifier);

        return true;
    }
}