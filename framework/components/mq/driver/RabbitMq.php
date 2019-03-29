<?php
namespace framework\components\mq\driver;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMq extends Mq
{
  protected $_mode = 'fanout';
  protected $_port;
  protected $_user;
  protected $_password;
  protected $_vhost = '/';
  protected $_connection;
  protected $_channel;
  protected $_exchange;
  protected $_queue;
  protected $_qos;
  protected $_router;
  protected $_consumerTag;

  public function __construct($config)
  {
    $this->_port = $config['port'] ?? '';
    $this->_user = $config['user'] ?? '';
    $this->_password =  $config['password'] ?? '';
    $this->_mode = $config['mode'] ?? 'fanout';
    $this->_exchange = $config['exchange'] ?? '';
    $this->_queue = $config['queue'] ?? '';
    $this->_qos = $config['qos'] ?? 1;
    $this->_router = $config['router'] ?? '';
    $this->_consumerTag = $config['consumer'] ?? '';

    if (!$this->_host) {
      $this->triggerThrowable(new \Exception('qmqp host can not be empty', 500));
    }
    if (!$this->_port) {
        $this->triggerThrowable(new \Exception('qmqp port can not be empty', 500));
    }
    if (!$this->_queue) {
        $this->triggerThrowable(new \Exception('qmqp queue can not be empty', 500));
    }
    if (!$this->_exchange) {
        $this->triggerThrowable(new \Exception('qmqp exchange can not be empty', 500));
    }
    if (!$this->_consumerTag) {
        $this->triggerThrowable(new \Exception('qmqp consumer can not be empty', 500));
    }

    $this->initAMQP();
    $this->declareMode();
  }

  protected function initAMQP()
  {
    $this->_connection = new AMQPStreamConnection($this->_host, $this->_port, $this->_user, $this->_password, $this->_vhost);
    $this->_channel = $this->_connection->channel();
  }

  protected function declareMode()
  {
    $consumerTag = 'consumer' . SYSTEM_WORK_ID;
    // 保证一次值接受_qos个
    $this->_channel->basic_qos(null, $this->_qos, null);
    switch ($this->_mode) {
        case 'direct':
          $this->_channel->queue_declare($this->_queue, false, true, false, false);
          $this->_channel->exchange_declare($this->_exchange, 'direct', false, true, false);
          /**
           * 这里进行绑定的时候如果只绑定了queue和exchange的话，queue和routekey的绑定就必须放到发布端
           * 如果这里把queue和router key 和 exchange都绑定了的话 发布端就不需要绑定了 
           * 如果在发布端没有绑定在消费端也没有绑定的话，发布的时候queue的名称默认是route的名称
           */
          // https://www.cnblogs.com/yxlblogs/p/10224553.html
          // 同一个队列绑定不同的routekey实现不同的消费
          if ($this->_router) {
            if (is_array($this->_router)) {
              foreach($this->_router as $item) {
                $this->_channel->queue_bind($this->_queue, $this->_exchange, $item);
              }
            } else {
              $this->_channel->queue_bind($this->_queue, $this->_exchange, $this->_router);
            }
          } else {
            $this->_channel->queue_bind($this->_queue, $this->_exchange);
          }
          $this->_channel->basic_consume($this->_queue, $consumerTag, false, false, false, false, [$this, 'processMessage']);
        break;
        case 'fanout':
        // 该模式下 会忽略route key  
        default:
        // 该模式下 消息会发送到每一个queue  然后再发送到每一个消费者，也就是同一条消息会发到不同的消费者(订阅同一个queue的)
          $this->_channel->queue_declare($this->_queue, false, false, false, true);
          $this->_channel->exchange_declare($this->_exchange, $this->_mode, false, false, true);
          $this->_channel->queue_bind($this->_queue, $this->_exchange);
          $this->_channel->basic_consume($this->_queue, $consumerTag, false, false, false, false, [$this, 'processMessage']);
        break;
    }
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
      $this->_handle && $this->_handle($message->body);
      $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    } catch (\Exception $e) {
      // 重新进入待处理消息队列中
        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
        throw new \Exception($e->getMessage(), 0);
    } catch (\Error $e) {
      // 第二个参数意思是 从任务队列中删除任务
      $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], false);
      throw new \Error($e->getMessage(), 0);
    }
  }

  public function setMode($mode)
  {
    $this->_mode = $mode;
  }

  public function setExchange($exchange)
  {
    $this->_exchange = $exchange;
  }

  public function setQueue($queue)
  {
    $this->_queue = $queue;
  }

  public function setQos($qos)
  {
    $this->_qos = $qos;
  }

  public function setRouter($router)
  {
    $this->_router = $router;
  }

  public function setConsumerTag($consumerTag)
  {
    $this->_consumerTag = $consumerTag;
  }

  public function start()
  {
    if (!$this->_start) {
      $this->_start = true;
      while (count($this->_channel->callbacks)) {
          $this->_channel->wait();
      }
    }
  }

  public function stop()
  {
    if ($this->_start) {
      $this->_channel->close();
      $this->_connection->close();
    }
    $this->_start = false;
  }
}