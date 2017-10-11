<?php
namespace framework\components\response;
use framework\base\Component;

class Response extends Component
{
    protected $_headers = array();
    protected $_code = 200;
    protected $_defaultType;
    protected $_defaultCharSet;
    protected $_curType;
    protected $_sendFile; //swoole专有数据
    protected $_contentTypes = array(
        'xml'  => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpg,image/jpeg,image/pjpeg',
        'gif'  => 'image/gif',
        'csv'  => 'text/csv',
        'txt' => 'text/plain',
        'html' => 'text/html,application/xhtml+xml,*/*',
        'pdf' => 'application/pdf',
        'xls' => 'application/x-xls',
        'apk' => 'application/vnd.android.package-archive',
        'doc' => 'application/msword',
        'zip' => 'application/zip'
    );

    protected function initHeader()
    {
        $this->_headers = array(
            'X-Powered-By' => 'esay-framework',
            'server' => 'esay-framework'
        );
    }

    protected function init()
    {
        $this->initHeader();
        $this->contentType('html');
    }

    public function noCache()
    {
        $this->addHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->addHeader('Pragma','no-cache');
//        header("Cache-Control: post-check=0, pre-check=0", false);
    }

    public function send($response, $result)
    {
        foreach ($this->_headers as $key=>$item)
        {
            $response->header($key,$item);
        }
        if (in_array($this->_curType, array('xml','html','json')))
        {
            $response->status($this->_code);
            if (!empty($result))
            {
                if (is_array($result)) {
                    $result = json_encode($result);
                }
                $response->write($result);
            }
            unset($result);
            return false;
        }
        else
        {
            unset($result);
            if (!empty($this->_sendFile)) {
                $response->sendfile($this->_sendFile);
            }
            $this->_sendFile = '';
        }

        $this->initHeader();
        $this->_curType = '';
        $this->_code = 200;
        return true;
    }

    public function addHeader($key, $header)
    {
        if(!empty($key) && !empty($header))
            $this->_headers[$key] = $header;
    }

    public function contentType($type, $charset = '')
    {
        $contentType = empty($this->_contentTypes[$type])?$this->_contentTypes[$this->getDefaultType()] : $this->_contentTypes[$type];
        $charset = empty($charset) ? $this->getDefaultCharSet(): $charset;
        $this->_curType = $type;
        $this->_headers['Content-Type'] = $contentType . '; charset=' . $charset;
    }

    protected function getDefaultType()
    {
        if(empty($this->_defaultType))
        {
            $this->_defaultType = $this->getValueFromConf('defaultType', 'html');
        }
        return $this->_defaultType;
    }

    protected function getDefaultCharSet()
    {
        if(empty($this->_defaultCharSet))
        {
            $this->_defaultCharSet = $this->getValueFromConf('charset', 'utf-8');
        }
        return $this->_defaultCharSet;
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function sendFile($path)
    {
        $this->_sendFile = $path;
    }
}