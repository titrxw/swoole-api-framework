<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;
use blog\lib\Web;

class Upload extends Web
{
    protected function rule()
    {
        return array(
            'indexApi' => array(
                'submit|post|参数错误'=>'require',
            )
        );
    }

    public function indexApi ()
    {
//        这里会把上传的参数也获取到  也是post方式，  这样的话就算需要token或者其他的验证也可以通过
        $path = $this->upload->save($this->request->post());
        if ($path) {
            return [200, $path];
        }
        return [501,'上传失败'];
//        if ($path) {
//            $this->getComponent($this->getSystem(),'imgzip')->resize($path, 600, 300);
//            return ['ret' => 200, 'data' => $path];
//        }
    }
}