<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace chat\controller;
use chat\lib\User;

class Conversation extends User
{
    private $_covM;

    protected function afterInit()
    {
        $this->_covM = $this->model('Conversation');
    }

    /**
     * @method get
     * 
     * @rule uid|get|参数错误 require
     * @rule text|get|发送内容错误 require
     */
    public function textApi()
    {
        $uid = $this->request->get('uid');
        $text = $this->request->get('text');
        if ($uid == $this->_uid) {
            return [501, '发送失败'];
        }

        $muser = $this->model('User')->info($this->_uid);
        $result = $this->_covM->save($this->_uid, $uid, $text, $muser['headimgurl'] ?? '');
        if (!$result) {
          return [501, '发送失败'];
        }


        $this->redis->getHandle()->sAdd($this->_uid.':covs', $uid);
        $this->redis->set($this->_uid . ':cov:' . $uid . ':last', ['name' => $user['name'] ?? '','headimgurl' => $user['headimgurl'] ?? '', 'type' => 'text', 'content' => $text, 'time' => time()]);
        $this->redis->getHandle()->sAdd($uid.':covs', $this->_uid);
        $user = $this->model('User')->info($uid);
        $this->redis->set($uid . ':cov:' . $this->_uid . ':last', ['name' => $muser['name'] ?? '','headimgurl' => $muser['headimgurl'] ?? '','type' => 'text', 'content' => $text, 'time' => time()]);

        
        $fd = $this->getFdByUid($uid);
        if ($fd) {
          $this->send($fd, [
              'union_id' => $this->_uid,
              'type' => 'text',
              'content' => $text,
              'name' => $muser['name'] ?? '',
              'headimgurl' => $muser['headimgurl'] ?? '',
              'time' => time()
          ]);
        }

        return [200, [
            'union_id' => $uid,
            'type' => 'text',
            'content' => $text,
            'name' => $user['name'] ?? '',
            'headimgurl' => $user['headimgurl'] ?? '',
            'time' => time()
        ]];
    }


    /**
     * @method get
     * 
     */
    public function listApi()
    {
        $covs = $this->redis->getHandle()->sMembers($this->_uid.':covs');
        $msgs = [];
        foreach ($covs as $key => $value) {
            # code...
            $msg = $this->redis->get($this->_uid . ':cov:' . $value . ':last');
            if ($msg) {
                $msg['time'] = date('Y-m-d H:i:s', $msg['time']);
                $msg['union_id'] = $value;
                $msgs[] = $msg;
            }
        }

        return [200,$msgs];
    }

    /**
     * @method get
     * 
     * @rule uid|get|参数错误 require
     * @rule page|get|参数错误 require|integer
     */
    public function historyApi()
    {
        $uid = $this->request->get('uid');
        $page = $this->request->get('page');
        if ($uid == $this->_uid) {
            return [200, []];
        }

        return [200,$this->_covM->list($this->_uid, $uid, $page)];
    }
}
