<?php 
namespace framework\process;

use framework\base\Container;
use framework\task\BaseTask;

class CrontabProcess extends Process
{
  protected $_isBusy = false;

  protected function afterInit()
  {
    $this->_handle->name('crontab-process');
  }

  protected function doChildMsg($msg)
  {
    if ($msg === 'busy') {
      $this->_isBusy = true;
    } else if ($msg == 'free') {
      $this->_isBusy = false;
      Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'client')->getClient()->free($this);
    }
  }

  public function toBusy()
  {
    $this->_isBusy = true;
  }

  public function isBusy()
  {
    return $this->_isBusy;
  }

  protected  function doProcessMsg(\swoole_process $worker, $taskObj = '')
  {
    $taskObj = json_decode($taskObj, true);
    if (is_array($taskObj))
    {
      if (!empty($taskObj['class']) && !empty($taskObj['func']))
      {
        try{
          $worker->write('busy');
          $obj = Container::getInstance()->getComponent(SYSTEM_APP_NAME, $taskObj['class']);

          if ($obj && $obj instanceof BaseTask)
          {
              $obj->run($taskObj['func'], array(), $worker, 0, 0);
              unset($obj);
          }
          else
          {
            $this->triggleThrowable(new \Exception('task at do:  class: ' . $taskObj['class'] . 'not found or not instance BaseTask'.
            ' or action: ' .$taskObj['func'] . ' not found', 500));
          }
        } catch (\Throwable $e) {
          $this->triggerThrowable($e);
        }
        finally {
          Container::getInstance()->finish(SYSTEM_APP_NAME);
          $worker->write('free');
        }
      }
    }
    return parent::doProcessMsg($worker);
  }
}