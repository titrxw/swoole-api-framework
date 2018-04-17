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
    public static function triggerThrowable (\Throwable $e)
    {
        throw $e;
    }

    public static function handleThrowable(\Throwable $e)
    {
        Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'exception')->handleException($e);
    }
}