<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:14
 */
namespace chat\lib;
use framework\web\WebSocket;

abstract class Web extends WebSocket
{
    public function before()
    {
        $result  = $this->validate();
        if ($result !== true)
        {
            return [ 500, $result];
        }
        return true;
    }

    protected function getUidByFd($fd = '')
    {
        $fd = $fd == '' ? $this->fd() : $fd;
        return $this->redis->get('fd:uid-' . $fd);
    }

    protected function getFdByUid($uid)
    {
        return $this->redis->get('uid:fd-' . $uid);
    }

    protected function setUidByFd($uid, $fd = '')
    {
        $fd = $fd == '' ? $this->fd() : $fd;
        $this->redis->set('fd:uid-' . $fd, $uid, USER_ONLINE_REDIS_EXPIRE);
        $this->redis->set('uid:fd-' . $uid, $fd, USER_ONLINE_REDIS_EXPIRE);

        return true;
    }

    protected function saveUser($user)
    {
        $uid = $user['union_id'];
        return $this->redis->set('u-' . $uid, $user , USER_ONLINE_REDIS_EXPIRE);
    }

    public function after($data = array())
    {
        if (is_array($data))
        {
            $data['ret'] = $data[0] ?? 200;
            $data['data'] = $data[0] == 200 ? $data[1] ?? '' : '';
            $data['msg'] = $data[0] == 200 ? '' : $data[1] ?? '';
            $data['action'] = \strtoupper($this->getController()) . '_' . \strtoupper(\rtrim($this->getAction(), 'Api')) . '_SEND';
            unset($data[0], $data[1], $data[2]);
        }
        return $data;
    }

    protected function send($fd, $data, $now = false)
    {
        $_data['data'] = $data;
        $_data['action'] = \strtoupper($this->getController()) . '_' . \strtoupper(\rtrim($this->getAction(), 'Api')) . '_RECV';
        parent::send($fd, $_data, $now);
    }
}