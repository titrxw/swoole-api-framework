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
        parent::addBaseComponents();

        $components = array(
            'server' => 'framework\\server\\Server',
            'log' => 'framework\\components\\log\\SwooleLog',
            'cache' => 'framework\\components\\cache\\Redis',
            'db' => 'framework\\components\\db\\Pdo',
            'taskManager' => 'framework\\task\\Task',
            'redis' => 'framework\\components\\cache\\Redis',
            'response' => 'framework\\components\\response\\SwooleResponse'
        );
        $this->_appConf['addComponentsMap'] = empty($this->_appConf['addComponentsMap']) ? array() : $this->_appConf['addComponentsMap'];
        $components = array_merge($components, $this->_appConf['addComponentsMap']);
        $this->_container->addComponents($components);

        unset($this->_appConf['addComponentsMap'], $components);
    }

    protected function beforeInit()
    {
        $this->_conf['components'] = empty($this->_conf['components']) ? array() : $this->_conf['components'];
        $this->_appConf['components'] = empty($this->_appConf['components']) ? array() : $this->_appConf['components'];
    }

    public static function run($conf)
    {
        if (PHP_SAPI !== 'cli')
        {
            echo 'have to run at cli';
            return false;
        }
        if (!empty($conf['server']))
        {
            unset($conf['server']);
        }
        $instance = new Application($conf);
        $instance->_container->getComponent('server')->start();
        unset($default, $conf, $instance);
    }
}