<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:14
 */
namespace blog\lib;
use framework\web\Api;
//
//use framework\web\Controller;
//
//abstract class Web extends Controller
//{
//    public function beforeAction()
//    {
//        $result  = $this->validate();
//        if ($result !== true)
//        {
//            return $this->ajax(null,500,$result);
//        }
//        $this->getComponent('session')->start();
//        return true;
//    }
//
//    public function afterAction($data = '')
//    {
//        $this->getComponent('session')->destroy();
//        return $data;
//    }
//}

abstract class User extends Api
{
    protected $user = [];

    public function beforeAction()
    {
        $this->response->addHeader('Access-Control-Allow-Origin', '*');
        $token = $this->request->post('token');
        if (!$token) {
            return ['ret' => 302, 'msg' => 'login false'];
        }
        if (!($user = $this->redis->get($token))) {
            return ['ret' => 301, 'msg' => 'login false'];
        }
//        $this->tokenBucket->run();
//        if (!$this->apireset->check($this->request->post('timestamp'),$this->request->post('nonce'),$this->request->post('sign'))) {
//            return ['ret' => 501, 'msg' => 'permission denied'];
//        }


        $result  = $this->validate();
        if ($result !== true)
        {
            return ['ret' => 500, 'msg' => $result];
        }
        $this->user = $user;
        return true;
    }

    public function afterAction($data = array())
    {
//        $this->getComponent('session')->destroy();
        if (is_array($data))
        {
            $data['ret'] = $data[0] ?? 200;
            $data['data'] = $data[0] == 200 ? $data[1] : '';
            $data['msg'] = $data[0] == 200 ? '' : $data[2];
            unset($data[0], $data[1], $data[2]);
        }
//        这里必须把结果结果返回去，该方法是放回结果前的结果
        return $data;
    }
}