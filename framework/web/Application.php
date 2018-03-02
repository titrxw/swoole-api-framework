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
            'log' => 'framework\\components\\log\\Log',
            // 'db' => 'framework\\components\\db\\Pdo',
            'cookie' => 'framework\\components\\cookie\\SwooleCookie',
            'taskManager' => 'framework\\task\\Task',
            'response' => 'framework\\components\\response\\SwooleResponse'
        );
        $this->_container->addComponents(SYSTEM_APP_NAME, $components);

        unset($components);
    }

    protected function beforeInit()
    {
        $this->_conf['components'] = $this->_conf['components']??[];
        $this->_appConf['components'] = [];
        $this->_conf['composer'] = $this->_conf['composer']??[];
        $this->_appConf['composer'] = [];
    }

    public static function run($conf)
    {
        if (PHP_SAPI !== 'cli')
        {
            echo 'have to run at cli';
            return false;
        }
        $instance = new Application($conf);
        $instance->_container->getComponent(SYSTEM_APP_NAME, 'server')->start();
        unset($default, $conf, $instance);
    }
}