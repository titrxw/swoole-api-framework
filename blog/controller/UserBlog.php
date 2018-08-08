<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;
use blog\lib\User;

class UserBlog extends User
{
    private $_blogM;
    private $_pageSize = 15;

    protected function afterInit()
    {
        $this->_blogM = $this->model('Blog');
    }

    public function saveApi()
    {
        $data = $this->request->post();
        $data['user_union_id'] = $this->user['union_id'];
        if (!empty($data['bu_id'])) {
            if (preg_match('/^b_\d{18}$/', $data['bu_id'])) {
                $data['union_id'] = $data['bu_id'];
                unset($data['bu_id']);
            } else {
                return [501, '参数错误'];
            }
        }


        if ($this->_blogM->save($data)) {
            return [200, 'ok'];
        }
        return [501, '操作失败'];
    }


    public function listApi ()
    {
        $page = $this->request->post('page', 1);
        return $this->_blogM->getList(['user_union_id' => $this->user['union_id'], 'LIMIT' => [($page - 1) * $this->_pageSize, $this->_pageSize]]);
    }

    public function detailApi()
    {
        $data = $this->_blogM->detail($this->request->post('bu_id'), $this->user['union_id']);
        if ($data) {
            return [200, $data];
        }
        return [501, '查询失败'];
    }
}