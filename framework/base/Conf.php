<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-2-2
 * Time: 上午2:16
 */
namespace framework\base;
use framework\traits\Exception;


class Conf extends Component
{
    use Exception;
    private $_config;


    public function getConfig($name)
    {
        $name = explode('.', $name);

        if (!isset($this->_config[$this->getSystem()][$name[0]])) {
//            加载配置文件
            $path = APP_ROOT . $this->getSystem() . '/conf/' . $name[0] . '.php';
            if (!file_exists($path)) {
                $this->triggerException('conf file ' . $name[0] . ' not exists', 500);
            }

            $this->_config[$this->getSystem()][$name[0]] = include $path;
        }

        $ret = $this->_config[$this->getSystem()];
        foreach ($name as $item) {
            $ret = $ret[$item] ?? '';
        }

        return $ret;
    }
}