<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/5
 * Time: 21:05
 */
namespace blog\model;

use framework\base\Model;

class Blog extends Model
{
    public function save($data)
    {
        if (!empty($data['union_id'])) {
            $result = $this->db()->update('blog', $data, ['union_id' => $data['union_id'], 'user_union_id' => $data['user_union_id']]);
        } else {
            $data['union_id'] = 'b_' . $this->helper->uniqueId();
            $data['timestamp'] = time();
            $result = $this->db()->insert('blog', $data);
        }

        if ($result && $result->rowCount()) {
            return true;
        }

        return false;
    }

    public function getList($where)
    {
        return $this->db()->select('blog',
            ['[><]user' => ['union_id' => 'user_union_id']],
            ['blog.union_id(bu_id)', 'blog.name(b_name)', 'user.name(u_name)', 'summary', 'tags', 'user_union_id(uu_id)', 'head_pic', 'views', 'is_show'],
            $where);
    }

    public function detail($union_id, $user_union_id, $is_show = '')
    {
        $where['blog.union_id'] = $union_id;
        if (!empty($user_union_id)) {
            $where['user_union_id'] = $user_union_id;
        }
        if (!empty($is_show)) {
            $where['is_show'] = $is_show;
        }


        $this->db()->update('blog', ['views[+]' => 1], ['union_id' => $where['union_id']]);
        $data = $this->db()->get('blog',
            ['[><]user' => ['union_id' => 'user_union_id']],
            ['blog.union_id(bu_id)', 'blog.name(b_name)', 'user.name(u_name)', 'summary', 'tags', 'user_union_id(uu_id)', 'head_pic', 'content', 'views', 'is_show'],
            $where);


        return $data;
    }
}