<?php

namespace framework\tool;
use framework\base\Component;


class Helper extends Component
{
    protected $_workId = 0;
    protected $_tools = [];

    protected function init()
    {
        $this->_workId = defined('SYSTEM_WORK_ID') ? SYSTEM_WORK_ID : 0;
    }

    protected function getTool($class)
    {
        $class = ucfirst($class);
        $class = '\framework\tool\\' . $class;
        if (empty($this->_tools[$class])) {
            $this->_tools[$class] = new $class($this->_workId);
        }
        return $this->_tools[$class];
    }

    public function uniqueId()
    {
        $instance = $this->getTool(__FUNCTION__);
       return $instance->nextId();
    }

    public function randStr($len = 8)
    {
        $codes = "0123456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY";

        $randStr = "";

        for($i=0; $i < $len; $i++)
        {
            $randStr .=$codes{mt_rand(0, strlen($codes)-1)};
        }

        return $randStr;
    }

    public function randNumber($len = 6)
    {
        $codes = "01234567890123456789012345678901234567890123456789";

        $randNumber = "";
        $_len = strlen($codes) - 1;

        for($i=0; $i < $len; $i++)
        {
            $randNumber .=$codes{mt_rand(0, $_len)};
        }

        return $randNumber;
    }

    public function token($data, $expre = 7200, $prefx='blog')
    {
        $data = is_array($data) ? json_encode($data) : $data;
        $salt = microtime();
        $token = md5($prefx.$data.$this->_workId.$salt);
        $this->getComponent($this->getSystem(), 'redis')->set($token, $data, $expre);
        return $token;
    }
}