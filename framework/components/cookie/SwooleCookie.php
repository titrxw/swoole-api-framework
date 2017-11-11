<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-8
 * Time: 下午10:04
 */
namespace framework\components\cookie;

class SwooleCookie extends Cookie
{
    public function send($response = '')
    {
        foreach ($this->_cookies as $key => $item)
        {
            $response->cookie($key, ...$item);
        }
        $this->rollback();
    }
}