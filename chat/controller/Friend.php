<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace chat\controller;
use chat\lib\User;

class Friend extends User
{
    private $_friendM;

    protected function afterInit()
    {
        $this->_friendM = $this->model('Friend');
    }

    /**
     * @method get
     * 
     * @rule keyword|get|参数错误 require
     */
    public function findUserApi()
    {
        $keyword = $this->request->get('keyword');
        return [200, $this->_friendM->findUser($this->_uid, $keyword)];
    }

    // /**
    //  * @method get
    //  * 
    //  * @rule mobile|get|账号格式错误 regex|/^1[34578]\d{9}$/  
    //  */
    // public function findUserByMobileApi()
    // {
    //     $mobile = $this->request->get('mobile');
    //     if ($mobile == $this->_user['mobile']) {
    //         return [200, false];
    //     }

    //     return [200, $this->_friendM->findUserByMobile($this->_uid, $mobile)];
    // }

    /**
     * @method get
     * 
     * @rule uid|get|参数错误 require 
     */
    public function addApi()
    {
        $uid = $this->request->get('uid');
        // 添加请求记录

        $result = $this->_friendM->add($this->_uid, $uid);
        if ($result === HAS_SEND_ADD_REQUEST) {
            return [200, true];
        }
        if (!$result) {
            return [501, '添加失败'];
        }

        $fd = $this->getFdByUid($uid);
        if ($fd) {
            $this->send($fd, [
                'name' => $this->_user['name'],
                'mobile' => $this->_user['mobile'],
                'headimgurl' => $this->_user['headimgurl'],
                'union_id' => $this->_uid
            ]);
        }
        
        return [200, true];
    }

    /**
     * @method get
     * 
     * @rule uid|get|参数错误 require 
     */
    public function sureAddApi()
    {
        $uid = $this->request->get('uid');

        $result = $this->_friendM->sureAdd($this->_uid, $uid);
        if ($result) {
            $fd = $this->getFdByUid($uid);
            if ($fd) {
                $this->send($fd, [
                    'name' => $this->_user['name'],
                    'headimgurl' => $this->_user['headimgurl'],
                    'mobile' => $this->_user['mobile'],
                    'union_id' => $this->_uid
                ]);
            }
            return [200, $uid];
        }
        return [501, '添加失败'];
    }

    /**
     * @method get
     */
    public function addLogApi()
    {
        return [200, $this->_friendM->addLogs($this->_uid)];
    }

    /**
     * @method get
     */
    public function listApi()
    {
        return [200, $this->_friendM->list($this->_uid)];
    }
}
