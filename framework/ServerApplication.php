<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:45
 */
namespace framework;
use framework\base\Application;

class ServerApplication extends Application
{
    protected function addBaseComponents()
    {
        parent::addBaseComponents();

        $components = [
            'server' => 'framework\\server\\Server',
            'conf' => 'framework\\base\\Conf',
            'cookie' => 'framework\\components\\cookie\\SwooleCookie',
            'taskManager' => 'framework\\task\\Task',
            'header' => 'framework\\components\\response\\SwooleHeader',
            'response' => 'framework\\components\\response\\SwooleResponse',
            'seaslog' => 'framework\\components\\log\\SeasLog'
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

        $conf = require_file('framework/conf/base.php');
        
        try {
            switch ($command) {
                case 'start':
                    $pidFile = $conf['components']['server']['pid_file'] ?? '';
                    if ($pidFile && file_exists($pidFile)) {
                        echo 'server has stated';
                        return;
                    }
                    $instance = new static($conf);
                    $instance->_container->getComponent(SYSTEM_APP_NAME, 'server')->start($instance);
                    unset($default, $conf, $instance);
                    break;
                case 'stop':
                    $pidFile = $conf['components']['server']['pid_file'] ?? '';
                    if ($pidFile && file_exists($pidFile)) {
                        $pid = file_get_contents($pidFile);
                        posix_kill($pid,SIGTERM);
                        unlink($pidFile);
                    } else {
                        echo 'stop server failed';
                    }
                    break;
                case 'restart':
                    $pidFile = $conf['components']['server']['pid_file'] ?? '';
                    if ($pidFile && file_exists($pidFile)) {
                        $pid = file_get_contents($pidFile);
                        posix_kill($pid, SIGUSR1); // reload all worker
    //                    posix_kill($pid, SIGUSR2); // reload all task
                    } else {
                        echo 'restart server failed';
                    }
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
