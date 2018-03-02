<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/5
 * Time: 21:05
 */
namespace application\model;

use framework\base\Model;

class User extends Model
{
    public function getList()
    {
        return $this->db()->select('user', ['id' ,'name']);
    }
}