<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午9:06
 */
namespace framework\server;

use framework\base\Component;

class Server extends Component
{
    protected $_server = null;

    public function start()
    {
        if (!extension_loaded('swoole')) {
            throw new \Error('not support: swoole', 500);
        }

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
            case 'rpc':
                $this->_server = new RpcServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
        }
    }

    public function getServer()
    {
        return $this->_server;
    }
}