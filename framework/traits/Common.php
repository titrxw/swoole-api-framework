<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-7
 * Time: 下午10:11
 */
namespace framework\traits;

trait Common
{
    public function getSystem ()
    {
        return $_SERVER['CURRENT_SYSTEM'] ?? '';
    }
}