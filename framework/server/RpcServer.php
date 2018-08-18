<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;


use Thrift\Protocol\TBinaryProtocol;
use Thrift\TMultiplexedProcessor;


class RpcServer extends BaseServer
{
  protected $_process;

  protected function afterWorkStart(\swoole_server $serv, $workerId)
  {
    if ($this->isWork($workerId)) {
      $this->_process = new TMultiplexedProcessor();

      $services = $this->getValueFromConf('services', []);
      foreach ($services as $key => $value) {
        # code...
        $handler = new $value['handle']();
        $process = new $value['process']($handler);
        $this->_process->registerProcessor($key, $process);
      }
    }
    return parent::afterWorkStart($serv, $workerId);
  }

  protected function afterReceive(\swoole_server $serv, $fd, $from_id, $data)
  {
      $socket = new RpcSocket();
      $socket->setHandle($fd);
      $socket->buffer = $data;
      $socket->server = $serv;
      $protocol = new TBinaryProtocol($socket, false, false);

      try {
          // $protocol->fname = $this->serviceName;
          $this->_process->process($protocol, $protocol);
      } catch (\Throwable $e) {
        $this->handleThrowable($e);
      }
      return parent::afterReceive($serv, $fd, $from_id, $data);;
  }
}