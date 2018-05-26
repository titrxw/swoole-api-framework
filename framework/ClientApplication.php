<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:45
 */
namespace framework;

class ClientApplication extends \framework\base\Application
{
    protected function addBaseComponents()
    {
        parent::addBaseComponents();

        $components = [
            'client' => 'framework\\client\\Client',
            'conf' => 'framework\\base\\Conf',
            'cookie' => 'framework\\components\\cookie\\SwooleCookie',
            'taskManager' => 'framework\\task\\Task',
            'response' => 'framework\\components\\response\\SwooleResponse'
        ];
        $components = array_merge($components, $this->_conf['addComponentsMap'] ?? []);
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

    public static function run($command = 'start', $server = true)
    {
        if (PHP_SAPI !== 'cli')
        {
            echo 'have to run at cli';
            return false;
        }

        $conf = [
            'default' =>  require_file('framework/conf/base.php'),
            'app' => []
        ];
        
        try {
            switch ($command) {
                case 'start':
                    // $pidFile = $conf['default']['components']['server']['pid_file'] ?? '';
                    // if ($pidFile && file_exists($pidFile)) {
                    //     echo 'server has stated';
                    //     return;
                    // }
                    $instance = new static($conf);
                    $instance->_container->getComponent(SYSTEM_APP_NAME, 'client')->start();
                    unset($default, $conf, $instance);
                    break;
            }
        } catch (\Throwable $e) {
            if (DEBUG) {
                var_dump($e->getMessage() .' ' . $e->getFile() . ' ' . $e->getLine());
            } else {
                echo 'server ' . $command. '  failed';
            }
            self::handleThrowable($e);
        }
    }
}