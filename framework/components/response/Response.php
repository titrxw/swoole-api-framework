<?php
namespace framework\components\response;
use framework\base\Component;

class Response extends Component
{
    protected $_headers = [];
    protected $_code = 200;
    protected $_curType;
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

    public function send($result,$else='')
    {
        if (is_array($result)) {
            $result = json_encode($result);
        }
        if (DEBUG)
        {
            $elseContent = ob_get_clean();
            if (is_array($elseContent)) {
                $elseContent = json_encode($elseContent);
            }
            $result = $elseContent . $result;
            unset($elseContent);
        }

        echo $result;

        $this->rollback();
        unset($result, $response);
        return true;
    }

    public function addHeader($key, $header)
    {
        if($key && $header)
            $this->_headers[$key] = $header;
    }

    public function contentType($type, $charset = '')
    {
        $contentType = $this->_contentTypes[$type] ?? $this->_contentTypes[$this->getValueFromConf('defaultType', 'html')];
        $charset = empty($charset) ? $this->getValueFromConf('charset', 'utf-8') : $charset;
        $this->_curType = $type;
        $this->_headers['Content-Type'] = $contentType . '; charset=' . $charset;
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function ajax($data)
    {
        $this->noCache();
        $this->contentType('json');
        return $data;
    }

    protected function rediret($url)
    {
        $this->addHeader('Location', $url);
        $this->setCode(302);
        return '';
    }

    protected function rollback()
    {
        $this->initHeader();
        $this->_curType = '';
        $this->_code = 200;
    }
}