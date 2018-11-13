<?php
namespace framework\client;

use framework\base\Component;
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TMultiplexedProtocol;
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
  public function call($host, $port, $service, $method, ...$args)
  {
    $socket = new TSocket($host, $port);
    $transport = new TFramedTransport($socket);
    $protocol = new TBinaryProtocol($transport);
    $Service = new TMultiplexedProtocol($protocol, $service);
    $transport->open();

    $namespace = \substr($service, 0 , \strpos($service, 'Service'));
    $class = '\\services\\' . $service . 'Client';
    $client = new $class($Service);
    $ret = $client->$method(...$args);
    $transport->close();

    return $ret;
  }
}
