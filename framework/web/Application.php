<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:45
 */
namespace framework\web;

class Application extends \framework\base\Application
{
    protected function addBaseComponents()
    {
        $this->_appConf['addComponentsMap'] = empty($this->_appConf['addComponentsMap']) ? array() : $this->_appConf['addComponentsMap'];
        parent::addBaseComponents();
        $components = array(
            'server' => 'framework\\server\\Server',
            'log' => 'framework\\components\\log\\SwooleLog',
            'cache' => 'framework\\components\\cache\\Redis',
            'Pdo' => 'framework\\components\\db\\Pdo'
        );
        $this->_container->addComponents($components);
        $this->_container->addComponents($this->_appConf['addComponentsMap']);
        unset($this->_appConf['addComponentsMap'], $components);
    }

    protected function beforeInit()
    {
        $this->_conf['components'] = empty($this->_conf['components']) ? array() : $this->_conf['components'];
        $this->_appConf['components'] = empty($this->_appConf['components']) ? array() : $this->_appConf['components'];
    }

    public static function run($conf)
    {
        if (!empty($conf['server']))
        {
            unset($conf['server']);
        }
        $instance = new Application($conf);
        $instance->_container->getComponent('server')->start();
        unset($default, $conf, $instance);
    }
}