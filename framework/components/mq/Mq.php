<?php
namespace framework\mq;

use framework\base\Component;

class Mq extends Component
{
  protected $_handle;

  protected function init()
  {
    switch ($this->getValueFromConf('handle', 'rabbit')) 
    {
      case 'rabbit':
        $this->_handle = new \framework\mq\driver\RabbitMq($this->getValueFromConf('conf'));
      break;
      default :
        $this->triggerThrowable(new Exception('mq not support ' . $this->getValueFromConf('handle', 'rabbit'), 500));
      break;
    }
  }

  public function __call($function, $arguments)
  {
    if ($this->_handle) {
      $this->_handle->$function(...$arguments);
    } else {
      $this->triggerThrowable(new Exception('method ' . $function . ' not exists in class Mq', 500));
    }
  }

  public function __destruct()
  {
    if ($this->_handle) {
      $this->_handle->close();
    }
  }
}  