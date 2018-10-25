<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/5
 * Time: 21:05
 */
namespace chat\model;

use framework\base\Model;

class Conversation extends Model
{
    protected $_pageSize = 10;

    public function save($sendUid, $recvUid, $text, $sheadimgurl)
    {
        $roomId = $sendUid > $recvUid ? hash33($recvUid.$sendUid) : hash33($sendUid.$recvUid); 
        $result = $this->db()->insert('conversation', ['room_id' => $roomId, 's_id' => $sendUid, 'r_id' => $recvUid, 'content' => $text, 'type' => 'text','s_headimgurl' => $sheadimgurl, 'timestamp' => time()]);
        return $result->rowCount() ? true : false;
    }

    public function list($sendUid, $recvUid, $page)
    {
        $roomId = $sendUid > $recvUid ? hash33($recvUid.$sendUid) : hash33($sendUid.$recvUid); 
        $result =$this->db()->select('conversation',['s_id','content','type', 's_headimgurl', 'timestamp'], ['room_id' => $roomId,'LIMIT' => [($page - 1) * $this->_pageSize, $this->_pageSize], 'ORDER' => ['timestamp' => 'DESC']]);
        if (!$result) {
            return [];
        }
        $result = \array_combine(\array_column($result, 'timestamp'), $result);
        ksort($result);
        $result = array_values($result);
        // data.isMy = true;
    //   data.sendStatus = "success";
        foreach ($result as &$value) {
            # code...
            $value['isMy'] = false;
            $value['sendStatus'] = "success";
            if ($value['s_id'] == $sendUid) {
                $value['isMy'] = true;
            }
            $value['headimgurl'] =  $value['s_headimgurl'];
            unset($value['s_headimgurl'], $value['r_headimgurl']);
        }

        return $result;
    }
} 