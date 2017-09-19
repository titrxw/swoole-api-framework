<?php
namespace framework\components\url;

class SwooleUrl extends Url
{
    public function getHost()
    {
        return $this->_server['host'];
    }

    public function getUrl()
    {
        return '';
    }

    public function getRequestUrl()
    {
        return $this->_server['request_uri'];
    }

    public function getMethod()
    {
        return $this->_server['request_method'];
    }

    public function getPathInfo()
    {
        return empty($this->_server['path_info']) ? '' : $this->_server['path_info'];
    }

}

