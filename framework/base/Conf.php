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
    protected $_supportYaf = false;

    protected function init()
    {
        $this->_supportYaf = $this->getValueFromConf('yaf', false);
    }

    public function get($name)
    {
        $name = explode('.', $name);

        if (!$this->_supportYaf) {
            if (!isset($this->_config[getModule()][$name[0]])) {
                //            加载配置文件
                $path = APP_ROOT . getModule() . '/conf/' . $name[0] . '.php';
                
                if (!file_exists($path)) {
                    $this->triggerThrowable('conf file ' . $name[0] . ' not exists', 500);
                }
                $this->_config[getModule()][$name[0]] = include $path;
            }
        } else {

        }
        

        $ret = $this->_config[getModule()];
        foreach ($name as $item) {
            $ret = $ret[$item] ?? '';
        }

        return $ret;
    }
}