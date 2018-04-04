<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;

use framework\web\Api;

class Upload extends Api
{
    public function indexApi ()
    {
        $path = $this->getComponent($this->getSystem(), 'upload')->save($this->request->post());
        if ($path) {
            $path = $this->getComponent($this->getSystem(),'imageZip')->resize($path);
            $this->getComponent($this->getSystem(),'imageZip')->resize($path, 100, 100);
            return ['ret' => 200, 'data' => $path];
        }


        return ['ret' => 501, 'msg' => '上传失败'];
    }
}