<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace chat\controller;
use chat\lib\Web;

class Common extends Web
{
    private $_userM;

    protected function afterInit()
    {
        $this->_userM = $this->model('User');
    }

    /**
     * @method post
     * 
     * @rule name|post|昵称错误 require
     * @rule mobile|post|账号格式错误 regex|/^1[34578]\d{9}$/  
     * @rule password|post|密码格式错误 require
     * @rule sure_password|post|确认密码格式错误 require
     */
    public function registerApi()
    {
        $name = $this->request->post('name');
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');
        $surePassword = $this->request->post('sure_password');

        if ($password !== $surePassword) {
            return [500, '确认密码错误'];
        }

        $user = $this->_userM->register($name, $mobile, $password);
        if ($user) {
            $this->saveUser($user);
            return [200, ['token' => $user['union_id']]];
        }

        return [500, '注册失败'];
    }

    /**
     * @method post
     * 
     * @rule mobile|post|账号格式错误 regex|/^1[34578]\d{9}$/  
     * @rule password|post|密码格式错误 require
     */
    public function loginApi ()
    {
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');

        $user = $this->_userM->login($mobile, $password);
        if ($user) {
            // $this->setUidByFd($user['union_id']);
            $this->saveUser($user);
            return [200,  ['token' => $user['union_id']]];
        }

        return [501, '登录失败'];
    }

    /**
     * @method get
     * 
     * @rule uid|get|参数错误 require 
     */
    public function userBindFdApi()
    {
        $uid = $this->request->get('uid');
        if (!$this->redis->has('u-'.$uid)) {
            return [501, '参数错误'];
        }
        
        $this->setUidByFd($uid);

        return [200, true];
    }
}
