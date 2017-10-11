<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-10-10
 * Time: 下午9:41
 */
namespace  framework\task;
use framework\base\Component;

abstract class BaseTask extends Component
{
    public function run($funcName, $params = array(), $server, $taskId, $fromId)
    {
        if (empty($funcName))
        {
            return false;
        }
        if (!method_exists($this, $funcName))
        {
            return false;
        }
        return $this->$funcName($params, $server, $taskId, $fromId);
    }
}