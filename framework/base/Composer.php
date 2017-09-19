<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/9
 * Time: 17:55
 */
namespace framework\base;

class Composer extends Base
{
    protected function init()
    {
        $this->_conf = $this->_appConf + $this->_conf;
        unset($this->_appConf);
    }

    public function checkComposer($name)
    {
        if (!empty($this->_conf[$name]))
        {
            return true;
        }
        return false;
    }

    public function getComposer($name, $params = array())
    {
        try
        {
            if ($this->_conf[$name] instanceof \Closure)
            {
                return $this->_conf[$name]($params);
            }
            unset($params);
            return null;
        }
        catch (\Error $e)
        {
            throw new \Error('composer ' . $name . 'not found' . $e->getMessage(), 500);
        }
    }
}