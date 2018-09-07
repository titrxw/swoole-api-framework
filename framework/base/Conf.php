<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-2-2
 * Time: 上午2:16
 */
namespace framework\base;


class Conf extends Component
{
    protected $_config;


    public function get($name)
    {
        $name = explode('.', $name);

        if (!isset($this->_config[$this->_haver][$name[0]])) {
            //            加载配置文件
            $path = APP_ROOT . $this->_haver . '/conf/' . $name[0] . '.php';
            
            if (!file_exists($path)) {
                $this->triggerThrowable(new \Error('conf file ' . $name[0] . ' not exists', 500));
            }
            $this->_config[$this->_haver][$name[0]] = include $path;
        }

        $ret = $this->_config[$this->_haver];
        foreach ($name as $item) {
            $ret = $ret[$item] ?? '';
        }

        return $ret;
    }
}
