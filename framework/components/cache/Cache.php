<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/10/30
 * Time: 21:00
 */
namespace framework\components\cache;
use framework\base\Component;

abstract class Cache extends Component
{
    protected $_handle = null;

    public function getCacheKey($name)
    {
        return getModule().($this->_conf['prefix'] ?? '') . $name;
    }

    public function getHandle()
    {
        return $this->_handle;
    }

    public function __destruct()
    {
        if ($this->_handle != null) {
            $this->_handle->close();
            $this->_handle = null;
        }
    }
}