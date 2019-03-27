<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:11
 */
namespace framework\web;

abstract class DistributeWebSocket extends WebSocket
{
    protected $_cache;

    protected function afterInit()
    {
      $this->_cache = $this->getComponent(\getModule(), $this->getValueFromConf('cache', 'redis'));
    }

    protected function send($fd, $data, $now = false)
    {
        if ($this->server->getServer()->exist($fd)) {
            $this->server->getServer()->push($fd, $data, $now);
        } else {
          // 找到对应的节点，发送
          // 然后通过消息发送到对应的节点
          // 统一发送
        }
    }
}
