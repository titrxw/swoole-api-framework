<?php
namespace framework\base;
use framework\traits\Throwable;


abstract class Base implements \ArrayAccess
{
    use Throwable;

    protected $_conf;

    public function __construct($conf = [])
    {
        $this->_conf = $conf;
        $this->init();
        unset($conf);
    }

    final public function getConf()
    {
        return $this->_conf;
    }

    final protected function getValueFromConf($key, $default = '')
    {
        if (!isset($this->{$key})) {
            $tmpKey = explode('.',$key);
            if (count($tmpKey) > 1)
            {
                $_confValue = empty($this->_conf[$tmpKey[0]]) ? null : $this->_conf[$tmpKey[0]];
                unset($tmpKey[0]);
                foreach ($tmpKey as $item)
                {
                    if ($_confValue)
                    {
                        $_confValue = $_confValue[$item] ?? null;
                    }
                }
            }
            else
            {
                $_confValue = !isset($this->_conf[$key]) ? null : $this->_conf[$key];
            }
            unset($tmpKey);

            $this->{$key} =  (!isset($_confValue) ? $default : $_confValue);
        }

        return $this->{$key};
    }

    protected function init()
    {
        return true;
    }


//    array接口的方法暂时不用
    public function offsetExists($offset)
    {
        if (isset($this->{$offset})) {
            return true;
        }

        return false;
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            $this->{$offset} = $this->getValueFromConf($offset);
            return $this->{$offset};
        }
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}