<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:14
 */
namespace application\lib;
use framework\web\Api;

abstract class Web extends Api
{
    public function beforeAction()
    {
        $result  = $this->validate();
        if ($result !== true)
        {
            return array(500, null, $result);
        }
        return true;
    }

    public function afterAction($data = array())
    {
        $data = array('ret' => empty($data[0]) ? 200 : $data[0],
            'data' => empty($data[1]) ? null : $data[1],
            'msg' => empty($data[2]) ? '' : $data[2]);
//        这里必须把结果结果返回去，该方法是放回结果前的结果
        return $data;
    }
}