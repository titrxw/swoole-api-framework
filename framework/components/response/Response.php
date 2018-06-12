<?php
namespace framework\components\response;
use framework\base\Component;
use framework\base\Container;

class Response extends Component
{
    protected $_header;

    protected function init()
    {
        $this->_header = Container::getInstance()->getComponent(SYSTEM_APP_NAME, 'header');
    }

    public function send($result,$else='')
    {
        $this->_header->send();

        if (is_array($result)) {
            $result = json_encode($result);
        }

        echo $result;

        $this->rollback();
        unset($result, $response);
        return true;
    }

    public function ajax($data)
    {
        $this->_header->noCache();
        $this->_header->contentType('json');
        return $data;
    }

    public function rediret($url)
    {
        $this->_header->add('Location', $url);
        $this->_header->setCode(302);
        return '';
    }

    protected function rollback()
    {
        $this->_header->rollback();
    }
}