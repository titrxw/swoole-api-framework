<?php
namespace framework\client;
use framework\base\Component;
use framework\crontab\CrontabClient;

class Client extends Component
{
  protected $_server = null;

    public function start()
    {
        if (!extension_loaded('swoole')) {
            throw new \Error('not support: swoole', 500);
        }

        switch ($this->getValueFromConf('type' , 'tcp'))
        {
            case 'tcp':
                $this->_server = new TcpClient(array(
                    'app' => $this->_appConf,
                    'default' => $this->_conf
                ));
                $this->_server->connect();
                break;
              
            case 'crontab':
            $this->_server = new CrontabClient(array(
                'app' => $this->_appConf,
                'default' => $this->_conf
            ));
            $this->_server->connect();
            break;
        }
    }
}