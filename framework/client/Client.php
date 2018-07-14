<?php
namespace framework\client;
use framework\base\Component;

class Client extends Component
{
    protected $_client = null;

    public function start()
    {
        if (!extension_loaded('swoole')) {
            throw new \Error('not support: swoole', 500);
        }

        switch ($this->getValueFromConf('type' , 'tcp'))
        {
            case 'tcp':
                $this->_client = new TcpClient($this->_conf);
                $this->_client->connect();
                break;
            case 'crontab':
                $this->_client = new CrontabClient($this->_conf);
                $this->_client->connect();
            break;
        }
    }

    public function getClient()
    {
        return $this->_client;
    }
}