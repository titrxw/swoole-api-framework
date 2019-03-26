<?php
namespace framework\components\mq\driver;

abstract class Base
{
  protected $_start;
  protected $_handle;
  protected $_host;

  public function __construct($config)
  {
    $this->_start = false;
    $this->_host = $config['host'] ?? '';
  }

  public function setDoer(\Closure $closure)
  {
    $this->_handle = $closure;
  }

  public function start()
  {
    
  }

  public function stop()
  {
    $this->_start = false;
  }

  public function __destruct()
  {
    $this->stop();
  }
}