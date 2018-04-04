<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-7
 * Time: 下午10:11
 */
namespace framework\traits;
use framework\base\Container;

trait Throwable
{
    public function triggerException (\Throwable $e)
    {
        throw $e;
    }

    public function handleException(\Throwable $e)
    {
        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'exception')->handleException($e);
    }
}