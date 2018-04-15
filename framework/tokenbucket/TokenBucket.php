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
    protected $_timeStep = 0;
    protected $_range;
    protected $_storeHandle = null;

    protected function init()
    {
        $this->_key = $this->getValueFromConf('key', '');
        $this->_max = $this->getValueFromConf('max', 0);
        $this->_addStep = $this->getValueFromConf('addStep', 0);
        $this->_timeStep = $this->getValueFromConf('timeStep', 0);
        $this->_range = $this->getValueFromConf('range', 0);
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

        $retIdentifier = $this->_storeHandle->lock($this->_key);
        if (!$this->_storeHandle->has($this->_key)) {
            $cur = $this->_max - 1;
            $this->_storeHandle->set($this->_key, ['cur' => $cur, 'last' => time()], $this->_range);
            $this->_storeHandle->getHandle()->lpush($this->_storeHandle->getCacheKey($this->_bucketListKey), $this->_key);
            if ($cur < 0) {
                $this->_storeHandle->unLock($this->_key, $retIdentifier);
                return false;
            }
        } else {
            $data = $this->_storeHandle->get($this->_key);
            if ($this->_timeStep > 0) {
                $add = floor(((time() - $data['last']) / $this->_timeStep) * $this->_addStep);
                $cur = $data['cur'] + $add;
            } else {
                $cur = $data['cur'];
            }

            --$cur;
            if ($cur < 0) {
                $this->_storeHandle->unLock($this->_key, $retIdentifier);
                return false;
            }

            $this->_storeHandle->set($this->_key, ['cur' => $cur, 'last' => time()], $this->_range);
        }

        $this->_storeHandle->unLock($this->_key, $retIdentifier);

        return true;
    }
}