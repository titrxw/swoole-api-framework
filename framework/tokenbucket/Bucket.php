<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-3-26
 * Time: 下午9:58
 */
namespace framework\tokenbucket;
use framework\base\Component;

class Bucket extends Component
{
    protected $_buckets;
    protected $_bucketListKey = 'bucket_token_redis_list';
    protected $_bucketsInstance;
    protected function init()
    {
        $redis = $this->getComponent($this->getSystem(), 'redis');
//        清空之前的token  防止不生效
        while ($key = $redis->getHandle()->lpop($redis->getCacheKey($this->_bucketListKey)))
        {
            $redis->rm($key);
        }
        $redis->rm($this->_bucketListKey);

        unset($redis);
    }

    public function validates($data = [])
    {
        $this->_buckets = $this->getValueFromConf('buckets', []);
        foreach ($this->_buckets as $key => $item) {
            if (isset($item['auto']) && $item['auto'] == false) {
                continue;
            }
            $this->getInstance($key)->run($this->getComponent(SYSTEM_APP_NAME, 'request'), $data);
        }
    }

    public function validate($item, $data = [])
    {
        $this->_buckets = $this->getValueFromConf('buckets', []);
        $this->getInstance($item)->run($this->getComponent(SYSTEM_APP_NAME, 'request'), $data);
    }

    protected function getInstance($name)
    {
        if (empty($this->_bucketsInstance[$name]) && !empty($this->_buckets[$name]['class'])) {
            $class = $this->_buckets[$name]['class'];
            $conf = array(
                'default' => [],
                'app' => $this->_buckets[$name]['conf'] ?? []
            );
            $instance = new $class($conf);
            if (!$instance instanceof TokenBucket) {
                unset($instance);
                $this->triggerThrowable(new \Exception('bucket ' . $class . ' must be instanceof TokenBucket', 500));
            }
            $instance->setStoreHandle($this->getComponent($this->getSystem(), 'redis'));
            $this->_bucketsInstance[$name] = $instance;
        }

        return $this->_bucketsInstance[$name];
    }
}