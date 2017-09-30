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
    protected function init()
    {
        $this->unInstall();
    }

    public function start()
    {
        if (!extension_loaded('swoole')) {
            throw new \Error('not support: swoole', 500);
        }

        switch ($this->getValueFromConf('type' , 'http'))
        {
            case 'http':
                $server = new HttpServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $server->start();
                break;
            case "webSocket":
                $server = new WebSocketServer(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $server->start();
                break;
        }
    }
}