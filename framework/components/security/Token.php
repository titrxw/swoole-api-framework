<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/10/25
 * Time: 20:53
 */

namespace framework\components\security;
use framework\base\Component;

class Token extends Component
{
    private $_handle;

    protected function init()
    {
      $this->_handle = $this->getComponent(\getModule(), $this->getValueFromConf('handle', 'aes'));
    }

    public function set($data)
    {
      return $this->_handle->encrypt($data);
    }

    public function get($data)
    {
      return $this->_handle->decrypt($data);
    }
}