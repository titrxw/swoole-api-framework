<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

use framework\base\Container;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqServer extends BaseServer
{
  protected $_start;
  protected $_mode;
  protected $_host;
  protected $_port;
  protected $_user;
  protected $_password;
  protected $_vhost;
  protected $_connection;
  protected $_channel;

  protected function init()
  {
    $this->_start = false;
    $this->_mode = $this->getValueFromConf('mq.mode', 'fanout');
    $this->_host = $this->getValueFromConf('mq.host');
    $this->_port = $this->getValueFromConf('mq.port');
    $this->_user = $this->getValueFromConf('mq.user');
    $this->_password =  $this->getValueFromConf('mq.password');
    $this->_vhost = $this->getValueFromConf('mq.host', '/');

    if (!$this->_host) {
        $this->triggerThrowable(new \Exception('qmqp host can not be empty', 500));
    }
    if (!$this->_port) {
        $this->triggerThrowable(new \Exception('qmqp port can not be empty', 500));
    }
    parent::init();
  }

  public function process_message($message)
  {
    // 这里需要解包协议
    try{
        $message = \json_decode($message->body);
        $this->doTask($message);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    } catch (\Exception $e) {
        $this->handleThrowable($e);
    }
  }

  protected function initAMQP()
  {
    $this->_connection = new AMQPStreamConnection($this->_host, $this->_port, $this->_user, $this->_password, $this->_vhost);
    $this->_channel = $this->_connection->channel();
  }

  protected function startConsumer()
  {
      if (!$this->_start) {
        $this->_start = true;
        while (count($this->_channel->callbacks)) {
            $this->_channel->wait();
        }
      }
  }

  protected function declareMode()
  {
    $exchange = $this->getValueFromConf('mq.exchange', 'fanout_exchange');
    $queue = $this->getValueFromConf('mq.queue', 'fanout_group');
    $consumerTag = 'consumer' . SYSTEM_WORK_ID;
    switch ($this->_mode) {
        case 'fanout':
        $this->_channel->queue_declare($queue, false, false, false, true);
        $this->_channel->exchange_declare($exchange, $this->_mode, false, false, true);
        $this->_channel->queue_bind($queue, $exchange);
        $this->_channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'processMessage']);
        break;
    }
  }

  protected function afterWorkStart(\swoole_server $serv, $workerId)
  {
    

    $this->initAMQP();
    $this->declareMode();
    $this->startConsumer();
  }

  protected function afterWorkStop(\swoole_server $serv, $workerId)
  {
    if ($this->_start) {
        $this->_channel->close();
        $this->_connection->close();
    }
    $this->_start = false;
    return true;
  }
}