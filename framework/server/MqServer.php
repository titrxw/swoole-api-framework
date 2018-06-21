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
  protected $_connection;
  protected $_channel;


  public function process_message($message)
  {
      echo "\n--------\n";
      echo $message->body;
      echo "\n--------\n";
      $this->handleThrowable(new \Exception($message->body . getmypid()));
      $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
      // Send a message with the string "quit" to cancel the consumer.
      if ($message->body === 'quit') {
          $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
      }
  }
  

  protected function afterWorkStart(\swoole_server $serv, $workerId)
  {
    $exchange = 'fanout_example_exchange';
$queue = 'fanout_group_1';
$consumerTag = 'consumer' . getmypid();
$this->_connection = new AMQPStreamConnection($this->_conf['mq']['host'], $this->_conf['mq']['port'], $this->_conf['mq']['user'], $this->_conf['mq']['pass'], $this->_conf['mq']['vhost']);
$this->_channel = $this->_connection->channel();

/*
    name: $queue    // should be unique in fanout exchange.
    passive: false  // don't check if a queue with the same name exists
    durable: false // the queue will not survive server restarts
    exclusive: false // the queue might be accessed by other channels
    auto_delete: true //the queue will be deleted once the channel is closed.
*/
$this->_channel->queue_declare($queue, false, false, false, true);
/*
    name: $exchange
    type: direct
    passive: false // don't check if a exchange with the same name exists
    durable: false // the exchange will not survive server restarts
    auto_delete: true //the exchange will be deleted once the channel is closed.
*/
$this->_channel->exchange_declare($exchange, 'fanout', false, false, true);
$this->_channel->queue_bind($queue, $exchange);
/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */

/*
    queue: Queue from where to get the messages
    consumer_tag: Consumer identifier
    no_local: Don't receive messages published by this consumer.
    no_ack: Tells the server if the consumer will acknowledge the messages.
    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
    nowait: don't wait for a server response. In case of error the server will raise a channel
            exception
    callback: A PHP Callback
*/
$this->_channel->basic_consume($queue, $consumerTag, false, false, false, false, [$this, 'process_message']);



    // Loop as long as the channel has callbacks registered
    while (count($this->_channel->callbacks)) {
      $this->_channel->wait();
    }
  }

  protected function afterWorkStop(\swoole_server $serv, $workerId)
  {
    $this->_channel->close();
    $this->_connection->close();
    return true;
  }
}