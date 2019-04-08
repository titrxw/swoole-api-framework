<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:11
 */
namespace framework\web;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

abstract class DistributeWebSocket extends WebSocket
{
    protected $_cache;

    protected function afterInit()
    {
      $this->_cache = $this->getComponent(\getModule(), $this->getValueFromConf('cache', 'redis'));
    }

    protected function send($fd, $data)
    {
        if (!parent::send($fd, $data, true)) {
          // 找到对应的节点，发送
          // 然后通过消息发送到对应的节点
          // 统一发送
        //   要优化
            $node = $this->_cache->get('ws_distribute_node:fd:' . $fd);
            if (!$node) {
                return true;
            }
            $exchange = $this->getValueFromConf('distribute_ws_mq.exchange');
            $queue = 'websocket-send-msg-' . $node;
            $connection = new AMQPStreamConnection(
                $this->getValueFromConf('distribute_ws_mq.host'), 
                $this->getValueFromConf('distribute_ws_mq.port'), 
                $this->getValueFromConf('distribute_ws_mq.user'), 
                $this->getValueFromConf('distribute_ws_mq.password'), 
                $this->getValueFromConf('distribute_ws_mq.vhost', '/'));
            $channel = $connection->channel();

            $channel->queue_declare($queue, false, true, false, false);
            $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
            $channel->queue_bind($queue, $exchange);
            $messageBody = [
                'fd' => $fd,
                'data' => $data
            ];
            $message = new AMQPMessage(json_encode($messageBody));
            $channel->basic_publish($message, $exchange);
            $channel->close();
            $connection->close();
        }
    }
}
