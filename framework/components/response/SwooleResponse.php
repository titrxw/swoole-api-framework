<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-11-8
 * Time: 下午9:33
 */
namespace framework\components\response;

class SwooleResponse extends Response
{
    protected $_sendFile; //swoole专有数据
    protected $_isDelete = true;

    public function send($response, $result = '')
    {
        $isEnd = false;
        $this->_header->send($response);

        if ($this->_sendFile)
        {
            $response->sendfile($this->_sendFile);
            if ($this->_isDelete) {
                unlink($this->_sendFile);
                $this->_isDelete = true;
            }
            $this->_sendFile = null;
            $isEnd = true;
        }
        else if (in_array($this->_header->getCurType(), array('xml','html','json', 'jpg', 'png', 'gif')))
        {
            $response->status($this->_header->getCode());
            if (is_array($result)) {
                $result = json_encode($result);
            }
            if ($result) {
                $response->write($result);
            }
        }
        $this->rollback();
        unset($result, $response);
        return $isEnd;
    }

    public function sendFile($path, $isDelete)
    {
        $this->_sendFile = $path;
        $this->_isDelete = $isDelete;
    }

    protected function rollback()
    {
        $this->_sendFile = '';
        parent::rollback();
    }
}