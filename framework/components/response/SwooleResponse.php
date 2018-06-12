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

    public function send($response, $result = '')
    {
        $isEnd = false;
        $this->_header->send($response);

        if ($this->_sendFile)
        {
            $response->sendfile($this->_sendFile);
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

    public function sendFile($path)
    {
        $this->_sendFile = $path;
    }

    protected function rollback()
    {
        $this->_sendFile = '';
        parent::rollback();
    }
}