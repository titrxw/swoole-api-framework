<?php
namespace framework\base;

abstract class Base
{
    protected $_conf;
    protected $_appConf;

    public function __construct($conf = [])
    {
        $this->_conf = $conf['default'] ?? [];
        $this->_appConf = $conf['app']?? [];
        $this->init();
        unset($conf);
    }

    public function getConf()
    {
        return $this->_conf;
    }

    public function getAppConf()
    {
        return $this->_appConf;
    }

    protected function getValueFromConf($key, $default = '')
    {
        $hashKey = md5($key);
        if (!isset($this->{$hashKey})) {
            $tmpKey = explode('.',$key);
            if (count($tmpKey) > 1)
            {
                $_confValue = $this->_conf[$tmpKey[0]] ?? null;
                $_appConfValue = $this->_appConf[$tmpKey[0]] ?? null;
                unset($tmpKey[0]);
                foreach ($tmpKey as $item)
                {
                    if ($_confValue)
                    {
                        $_confValue = $_confValue[$item];
                    }
                    if ($_appConfValue)
                    {
                        $_appConfValue = $_appConfValue[$item];
                    }
                }
            }
            else
            {
                $_confValue = !isset($this->_conf[$key]) ? null : $this->_conf[$key];
                $_appConfValue = !isset($this->_appConf[$key]) ? null : $this->_appConf[$key];
            }
            unset($tmpKey);

            $this->{$hashKey} =  !isset($_appConfValue) ?
                (!isset($_confValue) ? $default : $_confValue)
                : $_appConfValue;
        }

        return $this->{$hashKey};
    }

    protected function init()
    {
        return true;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}