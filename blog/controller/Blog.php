<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;
use blog\lib\Web;

class Blog extends Web
{
    private $_blogM;

    protected function rule()
    {
        return array(
            'detailApi' => array(
                'bu_id|post|参数错误' => 'regex|/^b_\d{18}$/'
            )
        );
    }

    protected function afterInit()
    {
        $this->_blogM = $this->model('Blog');
    }

    public function listApi ()
    {
        $page = $this->request->post('page', 1);
        $user_union_id = $this->request->post('uu_id');
        if (!empty($user_union_id) && preg_match('/^b_\d{18}$/', $user_union_id)) {
            $where['user_union_id'] = $user_union_id;
        }
        $where['is_show'] = 1;
        $where['LIMIT'] = [($page - 1) * $this->_pageSize, $this->_pageSize];


        $data = $this->_blogM->getList($where);
        if ($data) {
            return [200, $data];
        }

        return [501, '查询失败'];
    }

    public function detailApi()
    {
        $union_id = $this->request->post('bu_id');


        $data = $this->_blogM->detail($union_id, '', 1);
        if ($data) {
            return [200, $data];
        }
        return [501, '查询失败'];
    }
}