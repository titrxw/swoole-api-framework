<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/5
 * Time: 21:05
 */
namespace chat\model;

use framework\base\Model;

class User extends Model
{

    protected $_headers = [
        'https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=858959453,1988929777&fm=27&gp=0.jpg',
        'https://ss3.bdstatic.com/70cFv8Sh_Q1YnxGkpoWK1HF6hhy/it/u=377934783,1794723300&fm=27&gp=0.jpg',
        'https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=773579743,2271149885&fm=27&gp=0.jpg',
        'https://ss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=2612050777,637755064&fm=27&gp=0.jpg',
        'http://img5.imgtn.bdimg.com/it/u=1449317948,1646642637&fm=26&gp=0.jpg',
        'http://img4.imgtn.bdimg.com/it/u=992586811,744845853&fm=26&gp=0.jpg',
        'http://img0.imgtn.bdimg.com/it/u=2644037185,2973744129&fm=26&gp=0.jpg',
        'http://img5.imgtn.bdimg.com/it/u=3303741086,3211617265&fm=26&gp=0.jpg',
        'http://img2.imgtn.bdimg.com/it/u=1771944129,2197531217&fm=26&gp=0.jpg',
        'http://img4.imgtn.bdimg.com/it/u=1652165991,1699659763&fm=200&gp=0.jpg',
        'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=3797481993,1929347741&fm=27&gp=0.jpg',
        'http://img2.imgtn.bdimg.com/it/u=3244388588,4239675616&fm=26&gp=0.jpg'
    ];

    public function register($name, $mobile, $password) 
    {
        $userInfo = $this->db()->get('user', 'id', [
            'mobile' => $mobile,
        ]);
        if ($userInfo) {
            return false;
        }

        $password = $this->password->setPassword($password)->MakeHashStr();
        $salt = $this->password->GetHashSalt();

        $headimg = $this->_headers[mt_rand(0, count($this->_headers)-1)];
        $user = [
            'union_id' => 'u_' . \uniqueId(),
            'name' => $name,
            'mobile' => $mobile,
            'password' => $password,
            'headimgurl' => $headimg,
            'salt' => $salt,
            'timestamp' => time()
        ];

        $result = $this->db()->insert('user', $user);

        if ($result && $result->rowCount()) {
            unset($user['password'], $user['salt']);
            return $user;
        }

        return false;
    }

    public function login($mobile, $password)
    {
        $userInfo = $this->db()->get('user', ['union_id', 'mobile', 'password','salt', 'name', 'headimgurl'], [
            'mobile' => $mobile,
        ]);
        if (!$userInfo) {
            return false;
        }

        $result = $this->password->setPassword($password)
        ->setSalt($userInfo['salt'])
        ->setHash($userInfo['password'])
        ->validate();

        if (!$result) {
            return false;
        }

        unset($userInfo['password'], $userInfo['salt']);
        return $userInfo;
    }

    public function info($uid)
    {
        $result = $this->redis->get('u-' . $uid);
        if ($result) {
            return $result;
        }
        $result = $this->db()->get('user', ['headimgurl', 'mobile','name'],['union_id' => $uid]);
        if ($result) {
            $this->redis->set('u-' . $uid, $result , USER_ONLINE_REDIS_EXPIRE);
        }

        return $result;
    }

    public function password($oldPwd, $newPwd, $uid)
    {
        $userInfo = $this->db()->get('user', ['password','salt'], ['union_id' => $uid]);
        if (!$userInfo) {
            return false;
        }

        $result = $this->password->setPassword($oldPwd)
        ->setSalt($userInfo['salt'])
        ->setHash($userInfo['password'])
        ->validate();

        if (!$result) {
            return false;
        }

        $password = $this->password->setPassword($newPwd)->MakeHashStr();
        $salt = $this->password->GetHashSalt();       
        
        $result = $this->db()->update('user', ['password' => $password,'salt' => $salt], ['union_id' => $uid]);
        if ($result->rowCount()) {
            return true;
        }

        return false;
    }
}