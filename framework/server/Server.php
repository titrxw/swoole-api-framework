<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午9:06
 */
namespace framework\server;

use framework\base\Component;
use framework\crontab\CrontabServer;

class Server extends Component
{
    protected $_server = null;
    protected $_app;

    public function start($instance)
    {
        if (!extension_loaded('swoole')) {
            throw new \Error('not support: swoole', 500);
        }
        
        $this->_app = $instance;
        switch ($this->getValueFromConf('type' , 'http'))
        {
            case 'http':
                $this->_server = new HttpServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $this->_server->start();
                break;
            case "webSocket":
                $this->_server = new WebSocketServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $this->_server->start();
                break;
            case 'crontab':
                $this->_server = new CrontabServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $this->_server->start();
                break;
            case 'mq':
                $this->_server = new MqServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $this->_server->start();
            break;
        }
    }

    public function getServer()
    {
        return $this->_server;
    }

    public function getApp()
    {
        return $this->_app;
    }
}