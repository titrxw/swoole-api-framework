<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:14
 */
namespace chat\lib;

abstract class User extends Web
{
    protected $_uid;
    protected $_user;

    public function before()
    {
        $uid = $this->getUidByFd();
        if (!$uid) {
            return [302, 'login false'];
        }

        $result  = $this->validate();
        if ($result !== true)
        {
            return [501, $result];
        }

        $this->_uid = $uid;
        $this->_user = $this->getUser($uid);
        if (!$this->_user) {
            return [501, '请重新登陆'];
        }
        return true;
    }

    protected function getUser($uid)
    {
        return $this->redis->get('u-' . $this->_uid);
    }
}