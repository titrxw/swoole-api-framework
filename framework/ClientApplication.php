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

    public static function run($command = 'start', $server = true)
    {
        if (PHP_SAPI !== 'cli')
        {
            echo 'have to run at cli';
            return false;
        }

        try {
            switch ($command) {
                case 'start':
                    // $pidFile = $conf['default']['components']['server']['pid_file'] ?? '';
                    // if ($pidFile && file_exists($pidFile)) {
                    //     echo 'server has stated';
                    //     return;
                    // }
                    $instance = new static(require_file('framework/conf/base.php'));
                    $instance->_container->getComponent(SYSTEM_APP_NAME, 'client')->start();
                    unset($instance);
                    break;
            }
        } catch (\Throwable $e) {
            if (DEBUG) {
                echo ($e->getMessage() .' ' . $e->getFile() . ' ' . $e->getLine());
            } else {
                echo 'server ' . $command. '  failed';
            }
            self::handleThrowable($e);
        }
    }
}