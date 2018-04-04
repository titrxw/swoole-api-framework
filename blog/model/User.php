<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/5
 * Time: 21:05
 */
namespace blog\model;

use framework\base\Model;

class User extends Model
{
    public function login($mobile, $password)
    {
        $userInfo = $this->db()->get('user', ['union_id', 'mobile', 'password','salt', 'name', 'head_pic'], [
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
        $userInfo['token'] = $this->helper->token($userInfo);
        unset($userInfo['union_id']);
        return $userInfo;
    }

    public function register($mobile, $password) 
    {
        $userInfo = $this->db()->get('user', 'id', [
            'mobile' => $mobile,
        ]);
        if ($userInfo) {
            return false;
        }

        $password = $this->password->setPassword($password)->MakeHashStr();
        $salt = $this->password->GetHashSalt();

        $user = [
            'union_id' => 'u_' . $this->helper->uniqueId(),
            'name' => $mobile,
            'mobile' => $mobile,
            'password' => $password,
            'salt' => $salt,
            'timestamp' => time()
        ];

        $result = $this->db()->insert('user', $user);

        if ($result && $result->rowCount()) {
            unset($user['password'], $user['salt']);
            $user['token'] = $this->helper->token($user);
            unset($user['union_id'], $user['timestamp']);
            return $user;
        }

        return false;
    }
}