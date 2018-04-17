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
        $this->_appConf[$haver] = $conf;
    }

    public function checkComposer($haver, $name)
    {
        if (!empty($this->_conf[$name]))
        {
            return true;
        }
        if (!empty($this->_appConf[$haver][$name]))
        {
            return true;
        }
        return false;
    }

    public function getComposer($haver, $name, $params = [])
    {
        try
        {
            $composer = null;
            if ($haver == SYSTEM_APP_NAME) {
                $composer  = $this->_conf[$name];
            } else {
                $composer  = $this->_appConf[$haver][$name];
            }
            
            if ($composer instanceof \Closure)
            {
                return $composer($params);
            }
            unset($params, $composer);
            return null;
        }
        catch (\Exception $e)
        {
            $this->triggerThrowable(new \Exception('composer ' . $name . 'not found' . $e->getMessage(), 500));
        }
    }
}