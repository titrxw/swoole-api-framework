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

abstract class Web extends Api
{
    public function before()
    {
        $this->header->add('Access-Control-Allow-Origin', '*');
//        $this->tokenBucket->validates();
//        if (!$this->apireset->check($this->request->post('timestamp'),$this->request->post('nonce'),$this->request->post('sign'))) {
//            return ['ret' => 501, 'msg' => 'permission denied'];
//        }
        $result  = $this->validate();
        if ($result !== true)
        {
            return [500,$result];
        }
        return true;
    }

    public function after($data = array())
    {
//        $this->getComponent('session')->destroy();
        if (is_array($data))
        {
            $data['ret'] = $data[0] ?? 200;
            $data['data'] = $data[0] == 200 ? $data[1] ?? '' : '';
            $data['msg'] = $data[0] == 200 ? '' : $data[1] ?? '';
            unset($data[0], $data[1]);
        }
//        这里必须把结果结果返回去，该方法是放回结果前的结果
        return $data;
    }
}
