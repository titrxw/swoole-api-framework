<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

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

  public function processMessage($message)
  {
    // 这里需要解包协议
    /**
     * 如果任务执行成功而且消费者没有断开连接的话  该任务在服务端会一直保持unack状态  只有当消费者重新连接后才会切换为ready  重新下发
     * 如果任务执行异常导致消费者断开连接，任务会发送到下一个消费者
     *
     */
    try{
        $data = \json_decode($message->body);
        $this->doTask($data);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    } catch (\Exception $e) {
      // 重新进入待处理消息队列中
        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
        $this->handleThrowable($e);
    } catch (\Error $e) {
      // 第二个参数意思是 从任务队列中删除任务
      $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], false);
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
    $qos = $this->getValueFromConf('mq.qos', 1);
    $consumerTag = 'consumer' . SYSTEM_WORK_ID;
    // 保证一次值接受一个
    $this->_channel->basic_qos(null, $qos, null);
    switch ($this->_mode) {
        case 'direct':
          $this->_channel->queue_declare($queue, false, true, false, false);
          $this->_channel->exchange_declare($exchange, 'direct', false, true, false);
          $this->_channel->queue_bind($queue, $exchange);
          $this->_channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'processMessage']);
        break;
        case 'fanout':
        default:
        // 该模式下 消息会发送到每一个queue  然后再发送到每一个消费者，也就是同一条消息会发到不同的消费者
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