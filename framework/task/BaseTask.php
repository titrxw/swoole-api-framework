<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午9:41
 */
namespace  framework\task;

use framework\base\Component;
use framework\base\Container;

abstract class BaseTask extends Component
{
    protected function init()
    {
        // 执行完成后释放
        $this->unInstall();
    }

    public function db()
    {
        if (!$this->_dbHandle) {
            $this->_dbHandle = $this->getComponent(\getModule(), $this->getValueFromConf('db','meedo'));
        }
        return $this->_dbHandle;
    }

    public function run($funcName, $params = [], $server, $taskId, $fromId)
    {
        if (!$funcName)
        {
            $this->triggerThrowable(new \Error('function not be empty', 500));
        }
        if (!method_exists($this, $funcName))
        {
            $this->triggerThrowable(new \Error('function ' . $funcName . ' not exists', 500));
        }
        return $this->$funcName($params, $server, $taskId, $fromId);
    }

    /**
     * desc component 快捷获取方式
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        $module = \getModule();
        if (Container::getInstance()->hasComponent($module, $name)) {
            $this->$name = $this->getComponent($module, $name);
            return $this->$name;
        }
        if (Container::getInstance()->hasComponent(SYSTEM_APP_NAME, $name)) {
            $this->$name = $this->getComponent(SYSTEM_APP_NAME, $name);
            return $this->$name;
        }
        return null;
    }
}