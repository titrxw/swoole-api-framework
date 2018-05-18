<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-7
 * Time: 下午10:11
 */
namespace framework\server;
use framework\base\Container;

trait ExceptionTrait
{
    public function triggerException (\Exception $e)
    {
        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'exception')->handleException($e);
    }
} 