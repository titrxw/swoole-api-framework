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
    public function setAppComposers($haver, $conf)
    {
        $this->_conf[$haver] = $conf;
    }

    public function checkComposer($haver, $name)
    {
        if (!empty($this->_conf[$haver][$name]))
        {
            return true;
        }
        return false;
    }

    public function getComposer($haver, $name, $params = [])
    {
        try
        {
            $composer  = $this->_conf[$haver][$name];
            if ($composer instanceof \Closure)
            {
                return $composer($params);
            }
            unset($params, $composer);
            return null;
        }
        catch (\Throwable $e)
        {
            $this->triggerThrowable(new \Exception('composer ' . $name . ' not found ' . $e->getMessage(), 500));
        }
    }
}