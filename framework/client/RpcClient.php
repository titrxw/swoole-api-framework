<?php
namespace framework\client;

use framework\base\Component;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TFramedTransport;

require_once (APP_ROOT."framework/Thrift/ClassLoader/ThriftClassLoader.php");
$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', APP_ROOT. 'framework');
$loader->registerNamespace('services', APP_ROOT);
$loader->registerDefinition('services',  APP_ROOT);
$loader->register(TRUE);

class RpcClient extends Component
{
  public function call($host, $port, $class, $method, ...$args)
  {
    $socket = new TSocket($host, $port);
    $transport = new TFramedTransport($socket);
    $protocol = new TBinaryProtocol($transport);
    $transport->open();
    
    $client = new $class($protocol);
    $ret = $client->$method(...$args);
    $transport->close();

    return $ret;
  }
}