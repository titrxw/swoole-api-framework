<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-1
 * Time: 下午2:55
 */
namespace blog\lib;

use framework\base\Component;

class ApiReset extends Component
{
    private $_securityKey;
    private $_timestamp;
    private $_nonce;
    private $_sign;
    private $_timeStep;

    protected function init()
    {
        $this->_securityKey = $this->getValueFromConf('key', '');
        $this->_timeStep = $this->getValueFromConf('step', 60);
    }

    public function check($timestamp, $nonce, $sign)
    {
        $this->_timestamp = $timestamp;
        $this->_nonce = $nonce;
        $this->_sign = $sign;

        if ($this->checkSign() && $this->checkTime() && $this->checkNonce()) {
            return true;
        }
        return false;
    }

    private function checkSign()
    {
        if (md5('timestamp=' . $this->_timestamp . '&nonce=' . $this->_nonce . $this->_securityKey) === $this->_sign) {
            return true;
        }

        return false;
    }

    private function checkTime()
    {
        if ($this->_timestamp - time() > $this->_timeStep) {
            return false;
        }
        return true;
    }

    private function checkNonce()
    {
        $redis = $this->getComponent($this->getSystem(), 'redis');
        if ($redis->has('api_reset_' . $this->_nonce)) {
            $redis->set('api_reset_' . $this->_nonce, 1, $this->_timeStep);
            return false;
        } else {
            $redis->set('api_reset_' . $this->_nonce, 1, $this->_timeStep);
            return true;
        }
    }
}